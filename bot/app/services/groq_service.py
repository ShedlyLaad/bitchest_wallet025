import logging
from typing import Any
import httpx

from app.config import Settings

logger = logging.getLogger("bitchest_support_bot")


class GroqServiceError(Exception):
    def __init__(self, message: str, code: int, status_code: int = 500):
        super().__init__(message)
        self.message = message
        self.code = code
        self.status_code = status_code


class GroqService:
    def __init__(self, settings: Settings):
        self._settings = settings

    async def chat(self, messages: list[dict[str, str]]) -> str:
        key = self._settings.groq_api_key.strip()
        if not key or key.lower().startswith("your_") or "replace" in key.lower():
            logger.error("Missing GROQ_API_KEY in environment variables")
            raise GroqServiceError(
                message="Missing GROQ_API_KEY configuration",
                code=1001,
                status_code=500,
            )

        headers = {
            "Authorization": f"Bearer {key}",
            "Content-Type": "application/json",
        }
        payload: dict[str, Any] = {
            "model": self._settings.groq_model,
            "messages": messages,
            "temperature": 0.7,
            "max_tokens": 700,
        }

        try:
            async with httpx.AsyncClient(timeout=30.0) as client:
                response = await client.post(
                    self._settings.groq_url,
                    headers=headers,
                    json=payload,
                )
        except httpx.HTTPError as exc:
            logger.exception("Groq HTTP connection failure: %s", exc)
            raise GroqServiceError(
                message="Unable to reach AI provider",
                code=1002,
                status_code=503,
            ) from exc

        if response.status_code == 401:
            logger.error("Groq authentication failed (401): %s", response.text)
            raise GroqServiceError(
                message="Invalid API key configuration",
                code=1003,
                status_code=401,
            )

        if response.status_code >= 400:
            logger.error(
                "Groq API error status=%s body=%s",
                response.status_code,
                response.text,
            )
            raise GroqServiceError(
                message="AI provider request failed",
                code=1004,
                status_code=500,
            )

        data = response.json()
        try:
            return data["choices"][0]["message"]["content"]
        except (KeyError, IndexError, TypeError) as exc:
            logger.exception("Unexpected Groq response format: %s", data)
            raise GroqServiceError(
                message="Invalid AI provider response format",
                code=1005,
                status_code=500,
            ) from exc
