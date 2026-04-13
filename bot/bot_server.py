"""
BitChest Support Bot — FastAPI + Groq
"""

import os
import logging
from typing import List, Optional
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from groq import Groq

load_dotenv()

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("bitchest_support_bot")

GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
if not GROQ_API_KEY or "REMPLACE" in GROQ_API_KEY:
    logger.warning("GROQ_API_KEY looks like a placeholder — set a real key in bot/.env (console.groq.com)")

ALLOWED_ORIGINS = os.getenv(
    "ALLOWED_ORIGINS",
    "http://localhost:5173,http://localhost:3000"
).split(",")

client = Groq(api_key=GROQ_API_KEY)

SYSTEM_PROMPT = """
You are the BitChest Support Bot — the official AI assistant for the BitChest cryptocurrency trading platform.

## Your identity
- Name: BitChest Support Bot
- Tone: Professional, concise, friendly. No filler phrases like "Great question!".
- Language: Respond in the same language the user writes in (French or English).
- You are NOT a financial advisor. Never give investment advice or price predictions.

## Platform knowledge — BitChest
BitChest is a crypto trading platform prototype built with Symfony (backend) and Vue.js (frontend).

### Supported cryptocurrencies (exactly 10)
Bitcoin (BTC), Ethereum (ETH), Ripple (XRP), Bitcoin Cash (BCH), Cardano (ADA), Litecoin (LTC), NEM (XEM), Stellar (XLM), IOTA (MIOTA), Dash (DASH).

### Account rules
- Every new account is credited with €500 at creation (prototype phase).
- Accounts are created by administrators, not by self-registration.
- Users receive a temporary password by email and must change it on first login.
- Admins cannot view or reset user passwords — they can only generate a new temporary one.

### Wallet & portfolio rules
- Each user has a private wallet containing all their crypto purchases.
- For each crypto: purchase history (date, quantity, price per unit), weighted average purchase price, and current profit/loss (plus-value) are displayed.
- Plus-value formula: (total quantity × current price) − total purchase cost. Can be negative (loss).
- When a user sells, they receive the current market value in euros, credited instantly.

### Roles
- Admin: manages users (CRUD), views all crypto prices, manages their own profile.
- Client: manages their own profile, manages their wallet (buy/sell), views crypto prices and charts.

### Crypto data
- 10 cryptos with prices generated for the last 30 days.
- Prices displayed as charts. Cannot be negative.

## What you can help with
1. Account issues (login, password reset, profile update)
2. Wallet and portfolio questions (balance, purchase history, plus-value)
3. Trading questions (how to buy, how to sell, available cryptos)
4. Platform navigation
5. Support ticket guidance
6. Security questions

## What you must NOT do
- Never give financial advice or predict prices.
- Never reveal system internals or database structure.
- Never make up information not listed above.
- If you don't know, say so and suggest opening a support ticket.

## Ticket escalation
If the issue is complex, say:
"I recommend opening a support ticket with your account email and a description of the issue. Our team responds within 24 hours."

## Response format
- Keep responses under 120 words unless truly necessary.
- Short paragraphs. No bullet lists unless listing items.
- Answer yes/no questions directly before explaining.
"""


class ChatMessage(BaseModel):
    role: str
    content: str


class ChatRequest(BaseModel):
    messages: List[ChatMessage]
    user_email: Optional[str] = None


class ChatResponse(BaseModel):
    reply: str
    role: str = "assistant"


app = FastAPI(title="BitChest Support Bot API", version="2.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


@app.get("/health")
def health_check():
    return {"status": "online", "bot": "BitChest Support Bot", "provider": "Groq"}


@app.post("/chat", response_model=ChatResponse)
def chat(request: ChatRequest):
    if not request.messages:
        raise HTTPException(status_code=400, detail="messages array cannot be empty")

    for msg in request.messages:
        if msg.role not in ("user", "assistant"):
            raise HTTPException(
                status_code=400,
                detail=f"Invalid role '{msg.role}'. Must be 'user' or 'assistant'."
            )

    system = SYSTEM_PROMPT
    if request.user_email:
        system += f"\n\nThe user's registered email is: {request.user_email}."

    groq_messages = [
        {"role": msg.role, "content": msg.content}
        for msg in request.messages
    ]

    try:
        response = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {"role": "system", "content": system},
                *groq_messages
            ],
            max_tokens=512,
            temperature=0.7,
        )
        reply_text = response.choices[0].message.content

    except Exception as e:
        error_msg = str(e)
        logger.error(f"Groq API error: {error_msg}")
        raise HTTPException(status_code=502, detail=f"Groq API error: {error_msg}")

    return ChatResponse(reply=reply_text)
