import os
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel
from transformers import AutoTokenizer
from optimum.onnxruntime import ORTModelForFeatureExtraction
import numpy as np

ONNX_MODEL_PATH = "./onnx/e5-small"
MODEL_ID = os.getenv("MODEL_ID", "intfloat/multilingual-e5-small")

try:
    tokenizer = AutoTokenizer.from_pretrained(MODEL_ID)
    model = ORTModelForFeatureExtraction.from_pretrained(ONNX_MODEL_PATH)
    print(f"✅ Modelo ONNX e Tokenizer carregados com sucesso de: {ONNX_MODEL_PATH}")
except Exception as e:
    print(f"❌ Erro ao carregar o modelo ou tokenizer: {e}")
    # Em um ambiente de produção, você pode querer encerrar a aplicação aqui
    # ou ter um mecanismo de fallback. Para desenvolvimento, um print é suficiente.
    raise RuntimeError(f"Falha ao inicializar a aplicação: {e}")

# --- Definição da Aplicação FastAPI ---
app = FastAPI(
    title="Embedding API - multilingual-e5-small Quantized ONNX",
    description="API para gerar embeddings de texto usando o modelo multilingual-e5-small (ONNX quantizado)."
)

# --- Modelo Pydantic para a Requisição ---
class TextRequest(BaseModel):
    text: str

# --- Endpoint da API ---
@app.post(
    "/embed",
    summary="Gera embeddings de texto",
    response_description="O embedding gerado e sua dimensão",
    status_code=status.HTTP_200_OK
)
def embed_text(request: TextRequest):
    if not request.text or not request.text.strip():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="O texto não pode ser vazio ou conter apenas espaços."
        )
    input_text = "query: " + request.text

    inputs = tokenizer(
        input_text,
        return_tensors="np",
        padding=True, # Adiciona padding para lotes, embora aqui seja um item por vez
        truncation=True # Trunca textos muito longos para o tamanho máximo do modelo
    )

    try:
        outputs = model(**inputs)
    except Exception as e:
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Erro durante a inferência do modelo: {e}"
        )

    # Pooling mean para gerar vetor fixo
    embedding = outputs.last_hidden_state.mean(axis=1)[0]
    embedding_list = embedding.tolist()

    return {
        "embedding": embedding_list,
        "dimension": len(embedding_list)
    }
