from optimum.onnxruntime import ORTModelForFeatureExtraction, ORTQuantizer
from optimum.onnxruntime.configuration import AutoQuantizationConfig # Importe AutoQuantizationConfig
from transformers import AutoTokenizer
import os

# Passo 1: Defina o modelo e caminhos
model_id = "intfloat/multilingual-e5-small"
onnx_path = "onnx/e5-small"
quantized_path = "onnx/e5-small-quantized"

# Crie os diretórios se eles não existirem
os.makedirs(onnx_path, exist_ok=True)
os.makedirs(quantized_path, exist_ok=True)

# Passo 2: Converter para ONNX e salvar
print("🔄 Convertendo o modelo para ONNX...")
# O export=True já faz o trabalho de conversão
model = ORTModelForFeatureExtraction.from_pretrained(model_id, export=True)
model.save_pretrained(onnx_path)

print(f"✅ Modelo ONNX salvo em: {onnx_path}")

# Passo 3: Salvar também o tokenizer
tokenizer = AutoTokenizer.from_pretrained(model_id)
tokenizer.save_pretrained(onnx_path)

print(f"✅ Tokenizer salvo em: {onnx_path}")

# Passo 4: Quantizar o modelo ONNX
print("🔄 Iniciando a quantização...")

quantizer = ORTQuantizer.from_pretrained(onnx_path)

quantization_config = AutoQuantizationConfig.avx512_vnni(is_static=False, per_channel=False)

# Quantize e salve
quantizer.quantize(save_dir=quantized_path, quantization_config=quantization_config)

print(f"✅ Modelo quantizado salvo em: {quantized_path}")
