import os
import whisper
import torch
import json
import time

# Add ffmpeg to PATH
os.environ["PATH"] = r"C:\ffmpeg\bin;" + os.environ["PATH"]

def getJsonList():
    loop = 0
    # start timer
    start_time = time.time()
    with open('data.json', 'r') as f:
        data = json.load(f)
        en_data = data['en']
        cy_data = data['cy']

        for nid in en_data:
            print(f"Evaluating {loop} of {len(en_data) + len(cy_data)}")
            evaluate_model(nid, "en")
            loop += 1
        
        for nid in cy_data:
            print(f"Evaluating {loop} of {len(en_data) + len(cy_data)}")
            evaluate_model(nid, "cy")
            loop += 1

    # end timer
    end_time = time.time()
    elapsed_time = end_time - start_time

    # write elapsed time to file
    with open(f"elapsed_time_{model_type}.txt", "w") as f:
        f.write(f"Elapsed time: {elapsed_time:.2f} seconds\n")

# Custom function to format timestamps (seconds to HH:MM:SS.mmm)
def format_timestamp(seconds: float) -> str:
    hours = int(seconds // 3600)
    minutes = int((seconds % 3600) // 60)
    secs = seconds % 60
    # Format as HH:MM:SS.mmm with millisecond precision
    return f"{hours:02}:{minutes:02}:{secs:06.3f}"

# Custom function to write a VTT file from the transcription result
def write_vtt(result, vtt_file):
    with open(vtt_file, "w", encoding="utf-8") as f:
        f.write("WEBVTT\n\n")
        for segment in result.get("segments", []):
            start = format_timestamp(segment["start"])
            end = format_timestamp(segment["end"])
            text = segment["text"].strip()
            f.write(f"{start} --> {end}\n")
            f.write(text + "\n\n")
            
def evaluate_model(nid, lang):
    vtt_path = f"{model_type}/{nid}.vtt"
    if os.path.exists(vtt_path):
        print(f"File {vtt_path} already transcribed.")
        return

    audio_path = f"../download/audio/{nid}.mp3"
    if not os.path.exists(audio_path):
        print(f"Audio file {audio_path} does not exist. Skipping NID {nid}.")
        return

    audio = whisper.load_audio(audio_path)
    try:
        result = model.transcribe(audio, task="transcribe", verbose=False, language=lang, word_timestamps=True)
    except Exception as e:
        print(f"Error transcribing {nid}: {e}")
        return

    write_vtt(result, vtt_path)


if __name__ == "__main__":
    print("Loading model...")

    device_type = "cuda" # "cuda" or "cpu"

    # take user input for model type and device type
    model_type = input("Enter model type (tiny, base, small, medium, large, turbo): ").strip().lower()

    if(model_type not in ["tiny", "base", "small", "medium", "large", "turbo"]):
        print("Invalid model type. Defaulting to 'base'.")
        model_type = "base"

    if(model_type not in ["tiny", "base", "small", "medium", "turbo"]):
        device_type = "cpu"

    print(f"Running on {device_type} with model {model_type}...")

    model = whisper.load_model(model_type, device=device_type) # cuda or cpu
    print("Model loaded.")
    getJsonList()