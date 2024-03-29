import requests
import time

root_url = "http://172.20.10.5"

arr = [[900, 0, 0.4], [0, 200, 0.6], [-900, 0, 0.5], [0, 200, 0.6], [0, 0, 0]]
arr = [[0, 250, 0]]

# Off Position : servo_base:100, servo_1:78, servo_2:26

def move(x_err, m_init):
    try:
        payload = {
            'm_stop': 1,
            # 'mx_err': x_err,
            # 'my_err': 0, 
            'm_init': m_init,
            # 'servo_base': 50,
            # 'servo_grip': 0, # MAX 55
            # 'servo_1': 10, # MAX 85
            # 'servo_2': -10,
            # 'servo_relax': 0,
        }
        response = requests.get(root_url, params=payload, timeout=1)
        if response.status_code == 200:
            print("Response content:")
            print(response.text)
        else:
            print("Request failed with status code:", response.status_code)
    except requests.exceptions.RequestException as e:
        print("An error occurred:", e)


for item in arr:
    move(item[0], item[1])
    time.sleep(item[2])