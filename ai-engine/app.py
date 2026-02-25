import threading
import cv2
import mediapipe as mp
from gtts import gTTS
import pygame
import time
import os
import warnings
import signal
import json

warnings.filterwarnings("ignore")

mp_drawing = mp.solutions.drawing_utils
mp_hands = mp.solutions.hands

ai_running = True

# =======================
# PATH SETUP
# =======================
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PID_FILE = os.path.abspath(os.path.join(BASE_DIR, "..", "storage", "app", "ai_pid.txt"))
GESTURE_FILE = os.path.join(BASE_DIR, "gestures.json")

# =======================
# PID FUNCTIONS
# =======================
def save_pid():
    pid = os.getpid()
    os.makedirs(os.path.dirname(PID_FILE), exist_ok=True)
    with open(PID_FILE, "w") as f:
        f.write(str(pid))

def remove_pid():
    if os.path.exists(PID_FILE):
        os.remove(PID_FILE)

# =======================
# SIGNAL STOP HANDLER
# =======================
def stop_program(signum, frame):
    global ai_running
    ai_running = False
    print("AI Stopped...")

signal.signal(signal.SIGTERM, stop_program)
signal.signal(signal.SIGINT, stop_program)

# =======================
# LOAD GESTURES JSON
# =======================
def load_gestures():
    if os.path.exists(GESTURE_FILE):
        with open(GESTURE_FILE, "r") as f:
            return json.load(f)
    return []

# =======================
# TEXT TO SPEECH
# =======================
def play_audio(text):
    filename = f"voice_{text.replace(' ', '_').lower()}.mp3"
    tts = gTTS(text=text, lang='id')
    tts.save(filename)

    pygame.mixer.init()
    pygame.mixer.music.load(filename)
    pygame.mixer.music.play()

    while pygame.mixer.music.get_busy():
        time.sleep(0.1)

    pygame.mixer.quit()
    os.remove(filename)

# =======================
# DETECT FINGER PATTERN
# =======================
def detect_fingers(landmarks):
    index_tip = landmarks.landmark[8].y
    middle_tip = landmarks.landmark[12].y
    ring_tip = landmarks.landmark[16].y
    pinky_tip = landmarks.landmark[20].y

    index_base = landmarks.landmark[5].y
    middle_base = landmarks.landmark[9].y
    ring_base = landmarks.landmark[13].y
    pinky_base = landmarks.landmark[17].y

    fingers = [
        1 if index_tip < index_base else 0,
        1 if middle_tip < middle_base else 0,
        1 if ring_tip < ring_base else 0,
        1 if pinky_tip < pinky_base else 0
    ]

    return fingers

# =======================
# AI MAIN LOOP
# =======================
def run_ai():
    global ai_running

    cap = cv2.VideoCapture(0)

    cv2.namedWindow("AI Gesture Detection", cv2.WINDOW_NORMAL)
    cv2.setWindowProperty("AI Gesture Detection", cv2.WND_PROP_TOPMOST, 1)

    last_gesture = None
    last_time = 0

    with mp_hands.Hands(
        min_detection_confidence=0.7,
        min_tracking_confidence=0.7
    ) as hands:

        while ai_running:
            ret, frame = cap.read()
            if not ret:
                break

            frame = cv2.flip(frame, 1)
            rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            result = hands.process(rgb_frame)

            detected_name = None

            if result.multi_hand_landmarks:
                for hand_landmarks in result.multi_hand_landmarks:
                        mp_drawing.draw_landmarks(
                            frame,
                            hand_landmarks,
                            mp_hands.HAND_CONNECTIONS
                )

                fingers = detect_fingers(hand_landmarks)

                gestures = load_gestures()

                for g in gestures:
                    if g.get("active") and g.get("pattern") == fingers:

                        detected_name = g.get("name")
                        text_output = g.get("text")

                        cv2.putText(frame,
                                    text_output,
                                    (50, 80),
                                    cv2.FONT_HERSHEY_SIMPLEX,
                                    1.2,
                                    (0, 255, 255),
                                    2,
                                    cv2.LINE_AA)

                        if detected_name != last_gesture and time.time() - last_time > 2:
                            threading.Thread(
                                target=play_audio,
                                args=(text_output,),
                                daemon=True
                            ).start()

                            last_gesture = detected_name
                            last_time = time.time()

            cv2.imshow("AI Gesture Detection", frame)

            if cv2.waitKey(1) & 0xFF == 27:
                break

    cap.release()
    cv2.destroyAllWindows()
    remove_pid()

# =======================
# MAIN
# =======================
if __name__ == "__main__":
    print("AI Started...")
    print("Tekan ESC untuk keluar.")

    save_pid()
    run_ai()

    print("Program Closed")