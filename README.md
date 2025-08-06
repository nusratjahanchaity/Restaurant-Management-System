# Restaurant Management System

A file-based web application built using **PHP** and **XAMPP**, designed to manage restaurant orders, display sorted menus, and provide smart combo suggestions using **Dynamic Programming (0/1 Knapsack)** and **Greedy (Fractional Knapsack)** algorithms. Ideal for understanding how classic algorithms can enhance real-world user experiences.

---

## Project Info

- **Project Title:** Restaurant Management System  
- **Course Title:** Computer Algorithm Lab  
- **Course Code:** CSE-2422  
- **Semester:** 4th  
- **Students:**  
  - Salsabil Tasnim (C233445)  
  - Rehab Binte Alamgir (C233455)  
  - Nusrat Jahan Chaity (C233470)  
- **Instructor:** Miskatul Jannat Tuly  
- **Department:** CSE, IIUC  
- **Submission Date:** 07/08/2025  

---

## Project Features

- Login system with session management
- View and sort menu items from `menu.txt`
- Place, modify, and clear food orders
- Save and load orders from `orders.txt`
- Suggest:
  - **Best combo (full items)** using 0/1 Knapsack
  - **Approx combo (partial items)** using Fractional Knapsack
- Generate session summary on finish

---

## Algorithms Used

- **Merge Sort:** Sort menu items by price
- **0/1 Knapsack (Dynamic Programming):** Best full combo under budget
- **Fractional Knapsack (Greedy):** Best approximate combo under budget

---

## File Structure

```
Restaurant-Management-System/
│
├── welcome.php              # Login page
├── index.php                # Main dashboard
├── menu.txt                 # Food menu data
├── orders.txt               # User order history
├── dp_result.txt            # Best combo (DP)
├── greedy_result.txt        # Best combo (Greedy)
├── summary.txt              # Session summary (generated)
└── images/                  # UI background and icons
```

---

## Key Functionalities

- **Session Login:** Basic login with username/password
- **Menu Sorting:** Sorted by price using Merge Sort
- **Order Management:** Add, view, clear, and persist orders
- **Budget Optimization:**
  - Dynamic Programming for exact combos
  - Greedy for partial combos (value approximation)
- **Summary Generation:** Displays orders, combos, and total cost at logout

---

## Setup Instructions

1. **Install XAMPP**
   - Download from: https://www.apachefriends.org/download.html
   - Recommended: PHP 7.4+

2. **Clone the Project**
   ```bash
   git clone https://github.com/nusratjahanchaity/Restaurant-Management-System.git
   ```

3. **Move the Project to XAMPP Directory**
   - Move to: `C:\xampp\htdocs\`

4. **Start Apache Server**
   - Open XAMPP Control Panel → Start Apache

5. **Run the Project**
   - Visit: [http://localhost/Restaurant-Management-System](http://localhost/Restaurant-Management-System)

6. **Login Credentials**
   - **Username:** `user`  
   - **Password:** `12345`

---

## License

This project is for **educational use only**.