# Leave Approval System

A comprehensive **multi-step leave management and approval system** built with PHP and MySQL.  
Designed for organisations to streamline leave requests, approvals, and tracking across multiple roles and departments.

---

## What This System Can Do

- Employees can submit leave requests with details such as leave type, start/end dates, and reason.
- Multi-level approval workflow:
  - **Step 1:** Section Head reviews and approves or rejects leave requests from their department.
  - **Step 2:** Either the Head of Public Service (Head of PS) or the Authorized Officer reviews leave requests approved by the Section Head. Only one of them needs to approve or reject.
  - **Step 3:** Leave Officer gives the final approval after previous approvals.
- Track leave status at each step (pending, approved, rejected).
- Manage leave balances for different leave types (casual, sick, annual).
- Admin dashboard to manage users, departments, designations, and manual leave adjustments.
- Role-based access and views for Employees, Section Heads, Authorized Officers, Head of PS, Leave Officers, and Admins.
- Detailed leave reports per user, department, and approval status.
- Responsive UI with Tailwind CSS for clean and modern look.

---

## User Roles

| Role                 | Capabilities                                         |
| -------------------- | --------------------------------------------------- |
| **Employee**         | Submit leave requests, view leave status and balances. |
| **Section Head**     | Approve/reject leave requests from their department (Step 1). |
| **Authorized Officer** | Review and approve/reject leave requests approved by Section Head (Step 2). |
| **Head of Public Service (Head of PS)** | Also review and approve/reject leaves at Step 2 (alternative to Authorized Officer). |
| **Leave Officer**    | Final approval and leave record maintenance (Step 3). |
| **Admin**            | Manage users, departments, designations, and system settings. |

---

## Installation

1. **Clone the repository**

```bash
git clone https://github.com/yourusername/leave-approval-system.git
cd leave-approval-system
