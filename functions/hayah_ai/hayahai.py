import mysql.connector
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="hayahai_db"
)
cursor = db.cursor(dictionary=True)

cursor.execute("SELECT ProductID, Name, Category, Price, StockQuantity FROM products")
products = cursor.fetchall()

# Start of helpers. Stuff that makes this shit more bearable.

names = [p["Name"] for p in products]
vectorizer = TfidfVectorizer(stop_words="english")
tfidf = vectorizer.fit_transform(names)
similarity_matrix = cosine_similarity(tfidf)

STOPWORDS = {"is", "there", "available", "the", "a", "an", "do", "does", "have", "has"}

SYNONYMS = {
    "cookies": ["biscuit", "biscuits"],
    "biscuit": ["cookies", "biscuits"],
    "chips": ["crisps", "potato chips"],
    "crisps": ["chips"],
    "soda": ["cola", "soft drink", "pop"],
    "cola": ["soda", "soft drink"],
    "pop": ["soda", "cola"],
    "fries": ["french fries", "chips"],
    "ketchup": ["tomato sauce"],
    "tomato sauce": ["ketchup"],
    "candy": ["sweets", "confectionery"],
    "sweets": ["candy"],
    "milk": ["dairy"],
    "bread": ["loaf"],
    "loaf": ["bread"],
    "meat": ["beef", "pork", "chicken"],
    "beef": ["meat"],
    "pork": ["meat"],
    "chicken": ["meat"],
    "rice": ["grain"],
    "grain": ["rice"],
    "juice": ["drink", "beverage"],
    "drink": ["juice", "beverage"],
    "beverage": ["juice", "drink"]
}

def expand_tokens(tokens):
    expanded = []
    for t in tokens:
        expanded.append(t)
        if t in SYNONYMS:
            expanded.extend(SYNONYMS[t])
    return expanded

def get_similar(product_id):
    index = next(i for i, p in enumerate(products) if p["ProductID"] == product_id)
    scores = list(enumerate(similarity_matrix[index]))
    scores = sorted(scores, key=lambda x: x[1], reverse=True)[1:4]
    return [products[i]["Name"] for i, score in scores]

def get_matching_products(message):
    message = message.lower()
    tokens = [t for t in re.findall(r'\b\w+\b', message) if t not in STOPWORDS]
    expanded_tokens = expand_tokens(tokens)
    matches = []
    for p in products:
        name = p["Name"].lower()
        # token or its singular form in product name
        if any(token in name or token.rstrip('s') in name for token in expanded_tokens):
            matches.append(p)
    return matches

def contains_product_intent(message):
    return re.search(r'\b(stock|available|how many|price|cost|where|location)\b', message.lower())

def is_add_to_cart(message):
    message = message.lower()
    return (
        re.search(r'\b(add|put|include)\b.*\b(cart|basket|buy|it)?\b', message)
        or re.search(r'\b(yes|yeah|sure)\b.*\b(add|put|include)?\b.*\b(\d+)?\b', message)
    )

def extract_quantity(message):
    match = re.search(r'\badd\s+(\d+)', message.lower())
    return int(match.group(1)) if match else 1

def resolve_product_choice(message, options):
    message = message.lower()
    for p in options:
        if p["Name"].lower() in message:
            return p["ProductID"]
    return None

def is_negative_reply(message):
    return re.search(r'\b(no|nah|nope|not now|maybe later|cancel)\b', message.lower())

def is_nonsense(message):
    return not contains_product_intent(message) and not get_matching_products(message)

def find_closest_product(message):
    message = message.lower()
    message_vec = vectorizer.transform([message])
    sims = cosine_similarity(message_vec, tfidf)[0]
    best_index = sims.argmax()
    return products[best_index]["ProductID"]

# End of helpers. Yes, that many...

cursor.execute("SELECT * FROM customer_query WHERE response = '' ORDER BY created_at DESC LIMIT 1")
row = cursor.fetchone()

if row:
    user_id = row['user_id']
    user_input = row['message']
    user_input_lower = user_input.lower()

    if is_add_to_cart(user_input):
        cursor.execute("SELECT last_product_id FROM users WHERE id = %s", (user_id,))
        result = cursor.fetchone()
        product_id = result['last_product_id'] if result else None
        quantity = extract_quantity(user_input)

        if product_id:
            cursor.execute("SELECT Quantity FROM shopping_list WHERE UserID = %s AND ProductID = %s", (user_id, product_id))
            existing = cursor.fetchone()

            if existing:
                cursor.execute(
                    "UPDATE shopping_list SET Quantity = Quantity + %s WHERE UserID = %s AND ProductID = %s",
                    (quantity, user_id, product_id)
                )
            else:
                cursor.execute(
                    "INSERT INTO shopping_list (ProductID, UserID, Quantity) VALUES (%s, %s, %s)",
                    (product_id, user_id, quantity)
                )

            ai_reply = f"{quantity} item(s) added to your cart ✅"
        else:
            ai_reply = "Sorry, I’m not sure which item to add. Try asking about a product first."

        cursor.execute(
            "UPDATE customer_query SET response = %s, suggested_product_id = %s WHERE query_id = %s",
            (ai_reply, product_id, row['query_id'])
        )
        db.commit()
        print(ai_reply)
        cursor.close()
        db.close()
        exit()

    matches = get_matching_products(user_input)

    if len(matches) == 1:
        product_id = matches[0]["ProductID"]
        cursor.execute("UPDATE users SET last_product_id = %s WHERE id = %s", (product_id, user_id))
    elif len(matches) > 1:
        chosen_id = resolve_product_choice(user_input, matches)
        if chosen_id:
            product_id = chosen_id
            cursor.execute("UPDATE users SET last_product_id = %s WHERE id = %s", (product_id, user_id))
        else:
            options = [f"{p['Name']} (${p['Price']:.2f})" for p in matches]
            ai_reply = f"I found multiple products matching your query: {', '.join(options)}. Which one would you like?"
            cursor.execute(
                "UPDATE customer_query SET response = %s, suggested_product_id = NULL WHERE query_id = %s",
                (ai_reply, row['query_id'])
            )
            db.commit()
            print(ai_reply)
            cursor.close()
            db.close()
            exit()
    elif contains_product_intent(user_input):
        cursor.execute("SELECT last_product_id FROM users WHERE id = %s", (user_id,))
        result = cursor.fetchone()
        product_id = result['last_product_id'] if result else None

        if product_id:
            # keep context; proceed to reply generation using this product_id
            cursor.execute("UPDATE users SET last_product_id = %s WHERE id = %s", (product_id, user_id))
        else:
            ai_reply = f"Sorry, I couldn’t find any product matching '{user_input}'. Could you try a different name?"
            cursor.execute(
                "UPDATE customer_query SET response = %s, suggested_product_id = NULL WHERE query_id = %s",
                (ai_reply, row['query_id'])
            )
            db.commit()
            print(ai_reply)
            cursor.close()
            db.close()
            exit()
    else:
        product_id = find_closest_product(user_input)
        cursor.execute("UPDATE users SET last_product_id = %s WHERE id = %s", (product_id, user_id))

    if is_nonsense(user_input):
        ai_reply = "I’m not quite sure what you meant. Could you rephrase or ask about a specific item?"
        cursor.execute(
            "UPDATE customer_query SET response = %s, suggested_product_id = NULL WHERE query_id = %s",
            (ai_reply, row['query_id'])
        )
        db.commit()
        print(ai_reply)
        cursor.close()
        db.close()
        exit()

    if product_id:
        cursor.execute(
            "SELECT Name, Category, StockQuantity, Price FROM products WHERE ProductID = %s",
            (product_id,)
        )
        product = cursor.fetchone()

        if re.search(r'\b(stock|available|how many)\b', user_input_lower):
            ai_reply = f"There are {product['StockQuantity']} {product['Name']}(s) in stock. Would you like to add this to your cart?"
        elif re.search(r'\b(where|location)\b', user_input_lower):
            ai_reply = f"{product['Name']} is located in the {product['Category']} section."
        elif re.search(r'\b(price|cost|how much)\b', user_input_lower):
            ai_reply = f"{product['Name']} costs ${product['Price']:.2f} per unit."
        else:
            if is_negative_reply(user_input):
                ai_reply = "Alright, no problem. What else would you like to ask?"
            else:
                similar_products = get_similar(product_id)
                ai_reply = f"Based on your query, you might like: {', '.join(similar_products)}."
    else:
        ai_reply = "Sorry, I couldn't fully get you there. Try asking something else."

    cursor.execute(
        "UPDATE customer_query SET response = %s, suggested_product_id = %s WHERE query_id = %s",
        (ai_reply, product_id, row['query_id'])
    )
    db.commit()
    print(ai_reply)
else:
    print("No new messages to process.")

cursor.close()
db.close()