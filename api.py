import os
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from transformers import AutoTokenizer
from optimum.onnxruntime import ORTModelForFeatureExtraction
import numpy as np

app = FastAPI(title="Embedding API - multilingual-e5-small Quantized ONNX")

model_id = os.getenv("MODEL_ID", "intfloat/multilingual-e5-small")


tokenizer = AutoTokenizer.from_pretrained(model_id)
model = ORTModelForFeatureExtraction.from_pretrained("./onnx/e5-small")

class TextRequest(BaseModel):
    text: str

@app.post("/embed")
def embed_text(request: TextRequest):
    if not request.text.strip():
        raise HTTPException(status_code=400, detail="Texto não pode ser vazio")
    
    input_text = "query: " + request.text
    inputs = tokenizer(input_text, return_tensors="np")
    
    outputs = model(**inputs)
    
    # Pooling mean para gerar vetor fixo
    embedding = outputs.last_hidden_state.mean(axis=1)[0]
    embedding_list = embedding.tolist()
    
    return {
        "embedding": embedding_list,
        "dimension": len(embedding_list)
    }

