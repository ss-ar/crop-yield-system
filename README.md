---

```markdown
# Crop Yield Prediction Decision Support System (DSS)

A small-scale **Crop Yield Prediction Decision Support System** developed as a **university project**, focusing on a single district pilot in Uganda.  
The system helps estimate expected crop yield based on farm, soil, and weather inputs, and provides basic agricultural recommendations.

---

## 📌 Project Overview

This project implements a **web-based decision support system** that allows users to:
- Enter farm and seasonal details
- Predict crop yield (tons per acre and total yield)
- Assess risk levels (low / medium / high)
- Receive simple, actionable recommendations
- Store and review prediction history
- Export prediction data to CSV for reporting

The system is intentionally designed as a **small-scale prototype** to demonstrate concepts of data-driven decision support in agriculture.

---

## 🎯 Objectives

- To design a simple crop yield prediction system
- To demonstrate decision support using data inputs
- To store and analyze historical prediction records
- To provide a foundation for future machine learning integration

---

## 🧑‍💻 Technologies Used

- **PHP** (core application logic)
- **MySQL / MariaDB** (database)
- **Bootstrap 5 (offline)** for UI design
- **PDO** for secure database access
- **HTML / CSS / JavaScript**

> No external internet connection is required to run the system.

---

## 🗂️ Project Structure

```

crop-yield-system/
│
├── public/                 # Publicly accessible pages
│   ├── index.php
│   ├── login.php
│   ├── predict.php
│   ├── result.php
│   ├── history.php
│   ├── view.php
│   ├── logout.php
│   └── assets/
│       └── bootstrap/
│
├── app/
│   ├── config/
│   │   └── database.php
│   ├── helpers/
│   │   └── Auth.php
│   └── services/
│       ├── PredictionService.php
│       └── RecommendationService.php
│
├── database/
│   └── schema.sql
│
├── storage/
│   └── logs/
│
└── README.md

```

---

## 🧠 Prediction Logic (Summary)

The system uses a **baseline weighted scoring model** based on:
- Crop type
- Rainfall
- Average temperature
- Soil type
- Seed type
- Fertilizer usage
- Irrigation availability
- Farm size

The output includes:
- Predicted yield (tons per acre)
- Predicted total yield
- Risk level (low / medium / high)
- Recommendations based on input conditions

> This approach is suitable for academic demonstration and can be extended to advanced machine learning models in future work.

---

## 🗄️ Database

- Database name: `crop_yield_dss`
- Main tables:
  - `users` – system users (admin / user)
  - `predictions` – stored prediction records

The full database schema is available in:
```

database/schema.sql

````

---

## 🚀 Installation & Setup

### Requirements
- PHP 8.x
- MySQL or MariaDB
- Apache or PHP built-in server

### Steps

1. Clone the repository:
```bash
git clone https://github.com/ss-ar/crop-yield-system
cd crop-yield-system
````

2. Import the database:

```bash
mysql -u your_user -p < database/schema.sql
```

3. Configure database connection:
   Edit:

```
app/config/database.php
```

4. Start the development server:

```bash
php -S localhost:8000 -t public
```

5. Open in browser:

```
http://localhost:8000
```

---

## 🔐 Default Login (Example)

> Change these credentials after first login.

* **Username:** admin
* **Password:** admin123

---

## 📊 Features

* User authentication
* Crop yield prediction
* Risk assessment
* Recommendation generation
* Prediction history with filters
* CSV export
* Detailed record view
* Offline-friendly UI

---

## ⚠️ Limitations

* Uses a simplified predictive model
* Focused on a single-district pilot
* Does not integrate real-time weather or sensor data

---

## 🔮 Future Enhancements

* Integration of real machine learning models
* Multi-district or national scale deployment
* Real-time weather data integration
* Mobile-friendly interface
* IoT and sensor data support

---

## 🎓 Academic Note

This system was developed **strictly for academic purposes** to demonstrate system design, data handling, and decision support concepts in agriculture.