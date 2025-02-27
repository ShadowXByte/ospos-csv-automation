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

# Default values (can be overridden by user input)
DEFAULT_OSPOS_URL = "http://localhost/ospos/public/"
DEFAULT_USERNAME = "admin"
DEFAULT_PASSWORD = "pointofsale"
DEFAULT_CHROMEDRIVER_PATH = "C:\\path\\to\\chromedriver.exe"

def get_credentials():
    """Retrieve stored credentials or prompt for new ones."""
    username = keyring.get_password(SERVICE_NAME, "username") or DEFAULT_USERNAME
    password = keyring.get_password(SERVICE_NAME, "password") or DEFAULT_PASSWORD

    use_default = input(f"Use default credentials ({username})? (y/n): ").strip().lower()
    if use_default != 'y':
        username = input("Enter OSPOS Username: ")
        password = getpass.getpass("Enter OSPOS Password: ")  # Hides password input
        keyring.set_password(SERVICE_NAME, "username", username)
        keyring.set_password(SERVICE_NAME, "password", password)
        print("Credentials saved securely!")
    return username, password

def get_ospos_url():
    """Retrieve stored OSPOS URL or prompt for new one."""
    ospos_url = keyring.get_password(SERVICE_NAME, "ospos_url") or DEFAULT_OSPOS_URL

    use_default = input(f"Use default OSPOS URL ({ospos_url})? (y/n): ").strip().lower()
    if use_default != 'y':
        ospos_url = input("Enter OSPOS URL: ")
        keyring.set_password(SERVICE_NAME, "ospos_url", ospos_url)
        print("OSPOS URL saved!")
    return ospos_url

def get_csv_directory(section):
    """Retrieve stored CSV directory for section or prompt for new one."""
    csv_directory = keyring.get_password(SERVICE_NAME, f"csv_directory_{section}")
    if not csv_directory:
        csv_directory = input(f"Enter CSV Directory Path for {section}: ")
        keyring.set_password(SERVICE_NAME, f"csv_directory_{section}", csv_directory)
        print(f"CSV directory for {section} saved!")
    return csv_directory

def get_chromedriver_path():
    """Retrieve stored ChromeDriver path or prompt for new one."""
    chromedriver_path = keyring.get_password(SERVICE_NAME, "chromedriver_path") or DEFAULT_CHROMEDRIVER_PATH

    use_default = input(f"Use default ChromeDriver path ({chromedriver_path})? (y/n): ").strip().lower()
    if use_default != 'y':
        chromedriver_path = input("Enter ChromeDriver Path: ")
        keyring.set_password(SERVICE_NAME, "chromedriver_path", chromedriver_path)
        print("ChromeDriver path saved!")
    return chromedriver_path

def natural_sort_key(file_name):
    """Sort files in natural order (numbers in file names sorted correctly)."""
    return [int(text) if text.isdigit() else text.lower() for text in re.split(r'(\d+)', file_name)]

# Prompt user to choose which section to upload files to
def choose_section():
    print("Choose the section to upload CSV files to:")
    print("1. Items")
    print("2. Customers")
    choice = input("Enter the number for your choice (1 or 2): ").strip()
    if choice == '1':
        return "Items"
    elif choice == '2':
        return "Customers"
    else:
        print("Invalid choice. Defaulting to Items.")
        return "Items"

section = choose_section()

# Get user credentials, OSPOS URL, CSV directory, and ChromeDriver path
USERNAME, PASSWORD = get_credentials()
OSPOS_URL = get_ospos_url()
CSV_DIRECTORY = get_csv_directory(section)
CHROMEDRIVER_PATH = get_chromedriver_path()

# Initialize WebDriver
def setup_driver():
    options = webdriver.ChromeOptions()
    service = Service(CHROMEDRIVER_PATH)
    driver = webdriver.Chrome(service=service, options=options)
    driver.get(OSPOS_URL)
    return driver

# Login to OSPOS
def login(driver):
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "input-username"))).send_keys(USERNAME)
    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "input-password"))).send_keys(PASSWORD)
    WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.XPATH, "//button[@type='submit']"))).click()
    time.sleep(3)

# Ensure modal is closed before proceeding
def close_modal_if_open(driver):
    try:
        close_button = WebDriverWait(driver, 2).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Close')]")))
        close_button.click()
        WebDriverWait(driver, 5).until_not(EC.presence_of_element_located((By.CLASS_NAME, "modal-dialog")))
        time.sleep(2)
        print("Closed stuck modal.")
    except:
        pass

# Navigate to the CSV import page
def navigate_to_csv_import(driver, section):
    try:
        WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.LINK_TEXT, section))).click()
        time.sleep(3)
        open_csv_import_modal(driver)
    except Exception as e:
        print(f"Failed to navigate to {section} section: {e}")

# Open CSV Import Modal
def open_csv_import_modal(driver):
    try:
        csv_import_button = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, f"//button[contains(@data-href, '{section.lower()}/csvImport')]"))
        )
        csv_import_button.click()
        time.sleep(3)
    except Exception as e:
        print(f"Failed to open CSV import modal: {e}")

# Upload a single CSV file
def upload_csv(driver, file_path, first_upload):
    if first_upload:
        navigate_to_csv_import(driver, section)
    else:
        open_csv_import_modal(driver)

    try:
        file_input = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.NAME, "file_path")))
        file_input.send_keys(file_path)

        submit_button = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Submit')]"))
        )
        submit_button.click()

        time.sleep(5)
        WebDriverWait(driver, 10).until_not(EC.presence_of_element_located((By.CLASS_NAME, "modal-dialog")))
        time.sleep(2)

        print(f"Uploaded: {file_path}")
        return True
    except Exception as e:
        print(f"Failed to upload {file_path}: {e}")
        close_modal_if_open(driver)
        return False

# Process all CSV files
def upload_csv_files():
    driver = setup_driver()
    login(driver)

    files = [f for f in os.listdir(CSV_DIRECTORY) if f.endswith(".csv")]
    sorted_files = sorted(files, key=natural_sort_key)

    first_upload = True
    for filename in sorted_files:
        file_path = os.path.join(CSV_DIRECTORY, filename)
        upload_csv(driver, file_path, first_upload)
        first_upload = False

    print("Process Completed.")
    driver.quit()

# Run the script
upload_csv_files()
