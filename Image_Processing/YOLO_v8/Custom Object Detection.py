from torch import cuda
import cv2
from ultralytics import YOLO
import random
from time import time
from deep_sort_realtime.deepsort_tracker import DeepSort


class ObjectDetection:
    def __init__(self, videoCapture=1, windowResolution=480):
        self.capture_index = videoCapture
        self.device = 'cuda' if cuda.is_available() else 'cpu'
        print("Using Device: ", self.device)
        self.model = self.load_model()
        self.tracker = DeepSort(max_age=5)
        self.colors = [(random.randint(0, 255), random.randint(0, 255), random.randint(0, 255)) for j in range(10)]
        self.CLASS_NAMES_DICT = self.model.names
        self.setCap(windowResolution)
    
    def setCap(self, res):
        self.cap = cv2.VideoCapture(self.capture_index)
        assert self.cap.isOpened()
        width = 320 if res==240 else 640 if res==360 else 1280 if res==720 else 720
        print(f'Using Resolution : {res}x{width}')
        self.cap.set(cv2.CAP_PROP_FRAME_WIDTH, width)
        self.cap.set(cv2.CAP_PROP_FRAME_HEIGHT, res)

    def load_model(self):
        model = YOLO("yolov8n.pt")
        model.fuse()
        return model

    def predict(self, frame, show=False):
        results = self.model.predict(frame, show)
        return results[0]

    def exit(self):
        self.cap.release()
        cv2.destroyAllWindows()
  
    def __call__(self):
        while True:
            start_time = time()
            rect, frame = self.cap.read()
            results = self.predict(frame)

            detections = []
            for r in results.boxes.data.tolist():
                x1, y1, x2, y2, score, class_id = r
                x1, y1, x2, y2 = map(int, [x1, y1, x2, y2])
                detections.append([[x1, y1, x2, y2], score, class_id])

            tracked = self.tracker.update_tracks(detections, frame=frame)
            
            for track in tracked:   
                if not track.is_confirmed():
                    continue
                track_id = int(track.track_id)
                x1, y1, w, h = map(int, track.to_ltwh(orig=False))
                # print("2 >>>> ",x1, y1, w, h)
                cv2.rectangle(frame, (x1, y1), (w, h), (self.colors[track_id % len(self.colors)]), 3)
                cv2.circle(frame,(w,h),4,(255,0,255),-1)
                cv2.putText(frame,f'ID : {track_id}',(x1,y1),cv2.FONT_HERSHEY_COMPLEX,0.5,(255,0,0),1)

            end_time = time()
            fps = round(1/(end_time - start_time), 2)
            print(f'FPS: {fps}')

            cv2.imshow("RGB", frame)
            if(cv2.waitKey(30) == 27): break

        exit()
    
detector = ObjectDetection(videoCapture = 1, windowResolution = 360)
detector()