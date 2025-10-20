# 🇮🇹 Italian VAT Number Validator (PHP + XAMPP)

This PHP web application processes and validates **Italian VAT numbers** from a `.csv` file using Object-Oriented Programming principles. It handles user uploads, corrects malformed VAT numbers when possible, stores results in a database, and displays categorized outputs. Built with backend principles in mind, it includes a UI for manual testing as well.

---

## 📌 Features

- Upload and process CSV file with multiple VAT numbers
- Validate, correct, or reject VAT numbers based on format:
  - ✅ Valid: `IT12345678901`
  - 🛠️ Correctable: e.g. `12345678901` → `IT12345678901`
  - ❌ Invalid: e.g. `IT12345`, `hello123`, etc.
- Database storage of:
  - Valid VAT numbers
  - Corrected VAT numbers (with explanation)
  - Invalid VAT numbers
- Manual input form to test a single VAT number
- Categorized display of results
- Built with plain PHP (no frameworks), compatible with Apache/XAMPP or any PHP-enabled web server

---

## 🛠️ Technologies Used

- PHP (OOP)
- MySQL (via XAMPP)
- HTML/CSS (basic UI)
- Apache (via XAMPP)
- CSV file handling

---

## 🧪 VAT Number Validation Rules

A valid Italian VAT number must:
- Start with `"IT"`
- Followed by exactly **11 digits**

Examples:
- `IT12345678901` → ✅ Valid
- `12345678901` → 🛠️ Corrected to `IT12345678901`
- `IT12345` → ❌ Invalid
- `123-hello` → ❌ Invalid

---

## 📂 Folder Structure

