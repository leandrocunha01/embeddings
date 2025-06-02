import os
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel
from transformers import AutoTokenizer
from optimum.onnxruntime import ORTModelForFeatureExtraction
import numpy as np

model_name = os.getenv('MODEL_NAME', 'multilingual-e5-small')

ONNX_MODEL_PATH = f"./onnx/{model_name}"
MODEL_ID = os.getenv("MODEL_ID", f"intfloat/{model_name}")

try:
    tokenizer = AutoTokenizer.from_pretrained(MODEL_ID)
    model = ORTModelForFeatureExtraction.from_pretrained(ONNX_MODEL_PATH)
    print(f"✅ Modelo ONNX e Tokenizer carregados com sucesso de: {ONNX_MODEL_PATH}")
except Exception as e:
    print(f"❌ Erro ao carregar o modelo ou tokenizer: {e}")
    print(f"⚠️ Rode: python quantize_model.py")
    raise RuntimeError(f"Falha ao inicializar a aplicação: {e}")

app = FastAPI(
    title=f"Embedding API - {model_name} Quantized ONNX",
    description=f"API para gerar embeddings de texto usando o modelo {model_name} (ONNX quantizado)."
)


class TextRequest(BaseModel):
    text: str


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

    input_text = request.text

    inputs = tokenizer(
        input_text,
        return_tensors="np",
        padding=True,
        truncation=True
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

    # Normalização L2
    norm = np.linalg.norm(embedding)
    if norm > 0:
        embedding = embedding / norm

    embedding_list = embedding.tolist()

    return {
        "embedding": embedding_list,
        "dimension": len(embedding_list)
    }
