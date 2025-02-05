import keyring
import getpass
import os
import re
import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Service name for storing credentials in keyring
SERVICE_NAME = "OSPOS_AutoUploader"

# Default values
DEFAULT_OSPOS_URL = "http://localhost/ospos/public/"
DEFAULT_USERNAME = "admin"
DEFAULT_PASSWORD = "pointofsale"
DEFAULT_CHROMEDRIVER_PATH = os.path.normpath("C:/path/to/chromedriver.exe")

def get_credentials():
    username = keyring.get_password(SERVICE_NAME, "username") or DEFAULT_USERNAME
    password = keyring.get_password(SERVICE_NAME, "password") or DEFAULT_PASSWORD

    use_default = input(f"Use default credentials ({username})? (y/n): ").strip().lower()
    if use_default != 'y':
        username = input("Enter OSPOS Username: ")
        password = getpass.getpass("Enter OSPOS Password: ")
        keyring.set_password(SERVICE_NAME, "username", username)
        keyring.set_password(SERVICE_NAME, "password", password)
        print("Credentials saved securely!")
    return username, password

def get_ospos_url():
    ospos_url = keyring.get_password(SERVICE_NAME, "ospos_url") or DEFAULT_OSPOS_URL
    return ospos_url

def get_csv_directory(section):
    csv_directory = input(f"Enter CSV Directory Path for {section}: ")
    return os.path.normpath(csv_directory)  # Normalize path

def get_chromedriver_path():
    chromedriver_path = keyring.get_password(SERVICE_NAME, "chromedriver_path") or DEFAULT_CHROMEDRIVER_PATH
    return os.path.normpath(chromedriver_path)  # Normalize path

def setup_driver():
    options = webdriver.ChromeOptions()
    service = Service(CHROMEDRIVER_PATH)
    driver = webdriver.Chrome(service=service, options=options)
    driver.get(OSPOS_URL)
    return driver

def login(driver):
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "input-username"))).send_keys(USERNAME)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "input-password"))).send_keys(PASSWORD)
    WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[@type='submit']"))).click()
    time.sleep(3)

def upload_csv(driver, file_path, section):
    WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.LINK_TEXT, section))).click()
    time.sleep(2)
    file_input = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.NAME, "file_path")))
    file_input.send_keys(file_path)
    WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Submit')]"))).click()
    time.sleep(5)
    print(f"Uploaded: {file_path}")

def upload_csv_files():
    driver = setup_driver()
    login(driver)
    
    files = [f for f in os.listdir(CSV_DIRECTORY) if f.endswith(".csv")]
    files.sort(key=lambda x: [int(t) if t.isdigit() else t for t in re.split(r'(\d+)', x)])
    
    for filename in files:
        file_path = os.path.join(CSV_DIRECTORY, filename)
        upload_csv(driver, file_path, section)
    
    print("Process Completed.")
    driver.quit()

section = input("Enter the section to upload CSV files to (Items/Customers): ").strip().capitalize()
USERNAME, PASSWORD = get_credentials()
OSPOS_URL = get_ospos_url()
CSV_DIRECTORY = get_csv_directory(section)
CHROMEDRIVER_PATH = get_chromedriver_path()

upload_csv_files()
