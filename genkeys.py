import os

keys_needed = ["SERVER_RECEIVE_TRANSFER_KEY", "SERVER_SEND_TRANSFER_KEY", "BAZOOKA_MANAGER_KEY", "BAZOOKA_MANAGER_FILE_KEY"]

for key_needed in keys_needed:
    print("Key for \"" + key_needed + "\": " + os.urandom(16).hex())