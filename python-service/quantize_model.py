from optimum.onnxruntime import ORTModelForFeatureExtraction, ORTQuantizer
from optimum.onnxruntime.configuration import AutoOptimizationConfig
from transformers import AutoTokenizer

# Passo 1: Defina o modelo e caminhos
model_id = "intfloat/multilingual-e5-small"
onnx_path = "onnx/e5-small"
quantized_path = "onnx/e5-small-quantized"

# Passo 2: Converter para ONNX e salvar
print("🔄 Convertendo o modelo para ONNX...")
model = ORTModelForFeatureExtraction.from_pretrained(model_id, export=True)
model.save_pretrained(onnx_path)

print(f"✅ Modelo ONNX salvo em: {onnx_path}")

# Passo 3: Salvar também o tokenizer
tokenizer = AutoTokenizer.from_pretrained(model_id)
tokenizer.save_pretrained(onnx_path)

print(f"✅ Tokenizer salvo em: {onnx_path}")

# Passo 4: Quantizar o modelo ONNX
print("🔄 Iniciando a quantização...")

# Carregue o modelo ONNX salvo
quantizer = ORTQuantizer.from_pretrained(onnx_path)

# Configuração de otimização
dqconfig = AutoOptimizationConfig.avx512()  # ou .basic() se quiser compatibilidade ampla

# Quantize e salve
quantizer.quantize(save_dir=quantized_path, quantization_config=dqconfig)

print(f"✅ Modelo quantizado salvo em: {quantized_path}")

