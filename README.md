# Lorapz-Store

**Lorapz Store** is a modern web-based platform for digital content and creative services — including **eBook downloads**, **editing services**, and a **subscription system** for premium content.  
Built to deliver a smooth shopping and reading experience with both **user and admin dashboards**, Lorapz Store combines accessibility, responsive design, and data-driven management.

---

## Live Demo
> Coming soon  

---

## Features

### User Features
- Register, login, and manage personal profiles.
- Browse available eBooks and services.
- Purchase or subscribe for exclusive eBooks.
- Download purchased files securely.
- Comment and interact on posts or books.
- Light/Dark mode support for better readability.

### Admin Features
- Dashboard for managing users, products, and orders.
- Upload and edit eBook details (title, author, price, preview, etc.).
- Manage subscriptions and transactions.
- View analytics (sales, active users, top books).
- Control access level (Admin / Operator / User).

---

## Tech Stack

| Layer | Technologies |
|-------|---------------|
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla JS), BoxIcons |
| **Styling** | Responsive layout with Light/Dark theme |
| **Backend** | PHP (MySQL integration) |
| **Database** | MySQL / phpMyAdmin |
| **Hosting (Optional)** | XAMPP / Localhost / cPanel |
| **Version Control** | Git + GitHub |

---

## Database Overview

Main tables include:

| Table | Description |
|--------|--------------|
| `users` | Stores user accounts and roles (admin/operator/user). |
| `products` | Contains book/service information. |
| `orders` | Tracks purchases and subscriptions. |
| `comments` | Stores user comments on books/posts. |
| `subscriptions` | Records active and expired subscriptions. |

You can view relational structure through **phpMyAdmin → Designer View**.

---

## Installation Guide

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/lorapz-store.git
   cd lorapz-store
