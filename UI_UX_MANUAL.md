# 🎨 USER INTERFACE & USER EXPERIENCE MANUAL

## HOUSEKEEPING APPOINTMENT SYSTEM - UI/UX GUIDE

---

## I. INTRODUCTION

This manual provides a comprehensive guide to using the Housekeeping Appointment Management System. It covers all main interfaces, navigation flows, and step-by-step instructions for system operations.

### Target Users

- **Admin Users:** System administrators managing appointments, employees, and services
- **Support Staff:** Staff managing bookings and payments
- **System Managers:** Personnel managing employee assignments

---

## II. SYSTEM ENTRY POINT

### Login Page

**URL:** `http://localhost:8000` or `/login`

**Components:**

- Email input field
- Password input field
- "Remember Me" checkbox
- Login button
- "Forgot Password?" link
- Registration link (if enabled)

**Default Credentials:**

```
Email: admin@housekeeping.com
Password: password
```

**Interaction Flow:**

1. Enter email address
2. Enter password
3. Click "Login" button
4. System validates credentials
5. On success → Redirect to Dashboard
6. On error → Display error message

---

## III. DASHBOARD

### Overview

**URL:** `/dashboard`

**Purpose:** Central hub showing system statistics and quick action buttons

**Access:** After successful login, users land here

### Dashboard Components

#### A. Statistics Cards (Top Section)

Display real-time system metrics in card format:

```
┌─────────────────────────────────────────────────────────────────┐
│                      DASHBOARD STATISTICS                       │
├─────────────────────────────────────────────────────────────────┤
│
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  │  Total Apps      │  │  Pending Apps    │  │  In Progress     │
│  │      45          │  │       12         │  │       18         │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘
│
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│  │  Completed       │  │  Total Employees │  │  Total Services  │
│  │       15         │  │        8         │  │       7          │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘
│
│  ┌─────────────────────────────────────────────────────────────┐
│  │  Total Revenue: ₱ 125,450.00                                │
│  └─────────────────────────────────────────────────────────────┘
│
└─────────────────────────────────────────────────────────────────┘
```

**Card Details:**

- **Total Appointments:** Count of all appointments
- **Pending Appointments:** Count with status = "Pending"
- **In Progress:** Count with status = "In Progress"
- **Completed:** Count with status = "Completed"
- **Total Employees:** Total active/inactive employees
- **Total Services:** Count of all services available
- **Total Revenue:** Sum of all paid payments (payment_status = 'Paid')

#### B. Quick Action Buttons (Below Statistics)

```
┌─────────────────────────────────────────────────────────────────┐
│                    QUICK ACTIONS                                │
├─────────────────────────────────────────────────────────────────┤
│
│  [+ Create New Appointment] [+ Add Employee] [+ Add Service]    │
│
│  [View All Appointments] [View All Employees] [View Revenue]    │
│
└─────────────────────────────────────────────────────────────────┘
```

**Button Actions:**

- **+ Create New Appointment:** Navigate to `/appointments/create`
- **+ Add Employee:** Navigate to `/employees/create`
- **+ Add Service:** Navigate to `/services/create`
- **View All Appointments:** Navigate to `/appointments`
- **View All Employees:** Navigate to `/employees`
- **View Revenue:** Navigate to `/payments`

#### C. Navigation Menu

Located in sidebar/top navbar:

```
Dashboard
├─ Appointments
│  ├─ All Appointments
│  ├─ New Appointment
│  └─ Reports
├─ Employees
│  ├─ All Employees
│  ├─ Add Employee
│  └─ Status Report
├─ Services
│  ├─ Service Catalog
│  ├─ Add Service
│  └─ Pricing
├─ Payments
│  ├─ Payment History
│  ├─ Revenue Report
│  └─ Payment Methods
└─ Profile
   ├─ Edit Profile
   ├─ Change Password
   └─ Logout
```

---

## IV. APPOINTMENT MANAGEMENT

### A. View All Appointments

**URL:** `/appointments`

**Components:**

#### Table Display

```
┌──────────────────────────────────────────────────────────────────┐
│  APPOINTMENTS LIST                                               │
├──────────────────────────────────────────────────────────────────┤
│ Search: [_____________________] [Filter by Status ▼] [Export]   │
├──────────────────────────────────────────────────────────────────┤
│ ID │ Customer    │ Address    │ Date       │ Status  │ Actions  │
├────┼─────────────┼────────────┼────────────┼─────────┼──────────┤
│ 1  │ John Doe    │ 123 St Ave │ 2026-05-20 │ Pending │ ⚙️ ⚒️ 🗑️ │
│ 2  │ Jane Smith  │ 456 Oak    │ 2026-05-21 │ Done    │ ⚙️ ⚒️ 🗑️ │
│ 3  │ Mike Brown  │ 789 Pine   │ 2026-05-22 │ In Pro  │ ⚙️ ⚒️ 🗑️ │
│... │ ...         │ ...        │ ...        │ ...     │ ...      │
└────┴─────────────┴────────────┴────────────┴─────────┴──────────┘

[Previous] [1] [2] [3] [Next]
```

**Column Headers:**
| Column | Description |
|--------|-------------|
| ID | Appointment unique identifier |
| Customer | Customer name |
| Address | Service location |
| Date | Scheduled appointment date |
| Status | Current status (badge with color) |
| Actions | Edit, View, Delete buttons |

**Status Badge Colors:**

- 🟨 Yellow: Pending
- 🔵 Blue: In Progress
- 🟢 Green: Completed

**Actions:**

- **⚙️ Edit:** Update appointment details
- **⚒️ View:** See full details
- **🗑️ Delete:** Remove appointment
- **📊 Change Status:** Update appointment status

**Filtering Options:**

- Search by customer name
- Filter by status (Pending, In Progress, Completed)
- Sort by date
- Pagination (10, 25, 50 per page)

---

### B. Create New Appointment

**URL:** `/appointments/create`

**Form Layout:**

```
┌───────────────────────────────────────────────────────────────┐
│               CREATE NEW APPOINTMENT                          │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│ SECTION 1: CUSTOMER INFORMATION                             │
│ ─────────────────────────────────────────────────────────────│
│                                                               │
│  Customer Name:     [_____________________________]          │
│                     * Required, max 255 characters           │
│                                                               │
│  Address:           [_____________________________]          │
│                     [_____________________________]          │
│                     * Required, textarea                     │
│                                                               │
│  Area (sqm):        [____________]  [ⓘ Optional]            │
│                     * For pricing calculation               │
│                                                               │
│ SECTION 2: APPOINTMENT DETAILS                              │
│ ─────────────────────────────────────────────────────────────│
│                                                               │
│  Schedule Date:     [________________]  [Calendar 📅]       │
│  Schedule Time:     [________________]  [Clock 🕐]          │
│                     * Required, must be future date         │
│                                                               │
│  Notes:             [____________________________]          │
│                     [____________________________]          │
│                     * Optional, for special requests        │
│                                                               │
│ SECTION 3: ASSIGN SERVICES                                  │
│ ─────────────────────────────────────────────────────────────│
│                                                               │
│  Available Services:                                         │
│  ☐ Deep Cleaning (₱2,500)                                  │
│  ☐ Sofa Cleaning (₱1,500)                                  │
│  ☐ Carpet Cleaning (₱1,200)                                │
│  ☐ Window Cleaning (₱800)                                  │
│  ☐ Floor Polishing (₱55/sqm)                               │
│  ☐ Wall Washing (₱40/sqm)                                  │
│  ☐ General Cleaning (₱55/sqm)                              │
│                                                               │
│  For each selected service:                                  │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ ☑ Floor Polishing                                      ││
│  │   Quantity/Area: [_________] sqm                        ││
│  │   Price Override: [_________] ₱   [Default: ₱55.00]   ││
│  │   Subtotal: ₱2,200.00                                  ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│  Minimum Price: ₱2,750.00 (based on 50 sqm)               │
│  Total Price: ₱2,750.00                                     │
│                                                               │
│ SECTION 4: ASSIGN EMPLOYEES                                │
│ ─────────────────────────────────────────────────────────────│
│                                                               │
│  Available Employees (Active Only):                          │
│  ☐ John Smith - Senior Cleaner                             │
│  ☐ Maria Garcia - General Staff                            │
│  ☐ Luis Rodriguez - Team Lead                              │
│  ☑ Sofia Santos - Specialist                               │
│                                                               │
│  For each selected employee:                                 │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ ☑ Sofia Santos                                         ││
│  │   Task Assignment: [_____________________]             ││
│  │   (e.g., Floor polishing, final inspection)            ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│ ─────────────────────────────────────────────────────────────│
│  [← Back] [Save & Continue] [Save Draft]                    │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

**Form Fields Description:**

| Field          | Type       | Validation        | Purpose                   |
| -------------- | ---------- | ----------------- | ------------------------- |
| customer_name  | Text Input | Required, max 255 | Customer name             |
| address        | Textarea   | Required          | Service location          |
| area_sqm       | Number     | Optional, > 0     | Area in sqm for pricing   |
| schedule_date  | DateTime   | Required, future  | Appointment time          |
| notes          | Textarea   | Optional          | Special requests/notes    |
| services[]     | Checkboxes | At least 1        | Select services           |
| quantity[]     | Number     | Required, > 0     | Quantity/area per service |
| custom_price[] | Decimal    | Optional, > 0     | Price override            |
| employees[]    | Checkboxes | At least 1        | Select employees          |
| task[]         | Text       | Optional          | Task per employee         |

**Interactive Features:**

- **Price Calculation:** Auto-updates when services/quantities change
- **Service Filtering:** Show only "active" services
- **Employee Filtering:** Show only "Active" status employees
- **Quantity Field Label:** Changes based on pricing type
    - Fixed Price → "Quantity"
    - Per sqm → "Area (sqm)"
- **Price Override:** Shows default price, allow manual override
- **Validation Messages:** Real-time inline validation

**Submission:**

1. Click "Save & Continue" to create appointment
2. System validates all required fields
3. On success → Redirect to appointment details page
4. On error → Display error messages, stay on form

---

### C. Edit Appointment

**URL:** `/appointments/{id}/edit`

**Similar layout to Create form with:**

- Pre-filled data from database
- Current selections checked
- Read-only status field (display only)
- Current price display
- Updated section showing last modified date

---

### D. View Appointment Details

**URL:** `/appointments/{id}`

**Components:**

```
┌───────────────────────────────────────────────────────────────┐
│               APPOINTMENT DETAILS                             │
│                              [Edit] [Delete] [Back to List]   │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│ SECTION 1: CUSTOMER INFORMATION                             │
│ ─────────────────────────────────────────────────────────────│
│  Appointment ID:    #001                                     │
│  Customer Name:     John Doe                                 │
│  Address:           123 Street Avenue, City                 │
│  Area:              50 sqm                                   │
│  Scheduled:         2026-05-20 10:00 AM                     │
│  Status:            🟡 Pending                               │
│  Notes:             Deep cleaning + floor polish             │
│                                                               │
│ SECTION 2: ASSIGNED SERVICES                                │
│ ─────────────────────────────────────────────────────────────│
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 1. Deep Cleaning                                       ││
│  │    Price: ₱2,500.00 × 1 = ₱2,500.00                   ││
│  │    Created: 2026-05-19                                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 2. Floor Polishing                                     ││
│  │    Area: 50 sqm @ ₱55.00/sqm = ₱2,750.00             ││
│  │    Created: 2026-05-19                                 ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│  Total Price: ₱5,250.00                                     │
│  Minimum Price: ₱2,750.00 (50 sqm × ₱55)                  │
│  Final Estimated: ₱5,250.00                                │
│                                                               │
│ SECTION 3: ASSIGNED EMPLOYEES                              │
│ ─────────────────────────────────────────────────────────────│
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 1. Sofia Santos (Senior Cleaner)                       ││
│  │    Phone: +63 9XX XXXX XXX                             ││
│  │    Task: Floor polishing & inspection                  ││
│  │    Assigned: 2026-05-19 at 02:30 PM                    ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ 2. Maria Garcia (General Staff)                        ││
│  │    Phone: +63 9XX XXXX XXX                             ││
│  │    Task: General deep cleaning                         ││
│  │    Assigned: 2026-05-19 at 02:30 PM                    ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│ SECTION 4: STATUS & ACTIONS                                │
│ ─────────────────────────────────────────────────────────────│
│  Current Status: 🟡 Pending                                 │
│  [Change Status ▼]                                          │
│    └─ Mark as In Progress                                  │
│    └─ Mark as Completed                                    │
│                                                               │
│ SECTION 5: PAYMENT INFORMATION                             │
│ ─────────────────────────────────────────────────────────────│
│  Status: No payment recorded yet                            │
│  [Record Payment] (only available if status = Completed)    │
│  OR                                                         │
│  Payment ID: PAY-001                                        │
│  Amount: ₱5,250.00                                          │
│  Method: Cash                                               │
│  Status: 🟢 Paid                                             │
│  Recorded: 2026-05-21 03:15 PM                             │
│  [Edit Payment] [Delete Payment]                            │
│                                                               │
│ SECTION 6: TIMELINE                                         │
│ ─────────────────────────────────────────────────────────────│
│  2026-05-19 10:30 AM   Appointment Created                 │
│  2026-05-19 02:30 PM   Services Added (2 services)        │
│  2026-05-19 02:35 PM   Employees Assigned (2 staff)       │
│  2026-05-20 02:00 PM   Status Changed to In Progress      │
│  2026-05-21 03:00 PM   Status Changed to Completed        │
│  2026-05-21 03:15 PM   Payment Recorded - ₱5,250.00      │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

**Information Displayed:**

- Basic appointment information
- All assigned services with pricing
- All assigned employees with tasks
- Payment status and details
- Complete activity timeline
- Action buttons

**Available Actions:**

- Edit appointment details
- Change appointment status
- Record payment (if completed)
- Edit/delete payment (if exists)
- Delete appointment
- Return to list

---

## V. EMPLOYEE MANAGEMENT

### A. View All Employees

**URL:** `/employees`

```
┌──────────────────────────────────────────────────────────────────┐
│  EMPLOYEES LIST                                                  │
├──────────────────────────────────────────────────────────────────┤
│  Search: [_____________________] [Filter Status ▼]               │
├──────────────────────────────────────────────────────────────────┤
│ ID │ Name          │ Phone      │ Position      │ Status │ Act  │
├────┼───────────────┼────────────┼───────────────┼────────┼──────┤
│ 1  │ Sofia Santos  │ +63 9XX    │ Senior Clean  │ 🟢Act  │ ⚙️ 🗑️│
│ 2  │ Maria Garcia  │ +63 9XX    │ General Staff │ 🟢Act  │ ⚙️ 🗑️│
│ 3  │ Luis Rodriguez│ +63 9XX    │ Team Lead     │ 🟢Act  │ ⚙️ 🗑️│
│ 4  │ Anna Torres   │ +63 9XX    │ Specialist    │ 🔴Inac │ ⚙️ 🗑️│
└────┴───────────────┴────────────┴───────────────┴────────┴──────┘

[+ Add Employee]  [Previous] [1] [2] [Next]
```

**Status Indicators:**

- 🟢 Green: Active (available for assignment)
- 🔴 Red: Inactive (not available)

---

### B. Add/Edit Employee

**URL:** `/employees/create` or `/employees/{id}/edit`

```
┌───────────────────────────────────────────────────────────────┐
│               ADD NEW EMPLOYEE                                │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  Name:              [_____________________________]          │
│                     * Required, max 255 characters           │
│                                                               │
│  Phone Number:      [_____________________________]          │
│                     * Required, valid phone format          │
│                                                               │
│  Position/Role:     [_____________________________]          │
│                     * Required, e.g., Senior Cleaner       │
│                                                               │
│  Status:            ( ) Active    (●) Inactive              │
│                     * Default: Active                       │
│                                                               │
│ ───────────────────────────────────────────────────────────│
│  [← Back] [Save Employee]                                  │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

---

## VI. SERVICE MANAGEMENT

### A. Service Catalog

**URL:** `/services`

```
┌──────────────────────────────────────────────────────────────────┐
│  SERVICE CATALOG                                                 │
├──────────────────────────────────────────────────────────────────┤
│  Search: [_____________________]  [+ Add Service]                │
├──────────────────────────────────────────────────────────────────┤
│
│  CARD LAYOUT:
│
│  ┌──────────────────────────┐  ┌──────────────────────────┐
│  │ Deep Cleaning             │  │ Sofa Cleaning            │
│  │                           │  │                          │
│  │ Description:              │  │ Description:             │
│  │ Complete deep cleaning... │  │ Professional sofa...     │
│  │                           │  │                          │
│  │ Pricing Type: Fixed       │  │ Pricing Type: Fixed      │
│  │ Price: ₱2,500.00          │  │ Price: ₱1,500.00         │
│  │                           │  │                          │
│  │ [Edit] [Delete] [View]    │  │ [Edit] [Delete] [View]   │
│  └──────────────────────────┘  └──────────────────────────┘
│
│  ┌──────────────────────────┐  ┌──────────────────────────┐
│  │ Floor Polishing           │  │ General Cleaning         │
│  │                           │  │                          │
│  │ Description:              │  │ Description:             │
│  │ Professional floor...     │  │ General area cleaning... │
│  │                           │  │                          │
│  │ Pricing Type: Per sqm     │  │ Pricing Type: Per sqm    │
│  │ Price: ₱55.00 / sqm       │  │ Price: ₱55.00 / sqm      │
│  │                           │  │                          │
│  │ [Edit] [Delete] [View]    │  │ [Edit] [Delete] [View]   │
│  └──────────────────────────┘  └──────────────────────────┘
│
└──────────────────────────────────────────────────────────────────┘
```

**Card Information:**

- Service name (bold heading)
- Description preview
- Pricing type (Fixed or Per sqm)
- Base price
- Action buttons

---

### B. Add/Edit Service

```
┌───────────────────────────────────────────────────────────────┐
│               ADD NEW SERVICE                                 │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  Service Name:      [_____________________________]          │
│                     * Required, must be unique              │
│                                                               │
│  Description:       [____________________________]          │
│                     [____________________________]          │
│                     * Optional, detailed description        │
│                                                               │
│  Pricing Type:      ○ Fixed Price                           │
│                     ○ Per Square Meter (sqm)               │
│                     * Determines how price is calculated    │
│                                                               │
│  Base Price:        [____________] ₱                        │
│                     * Required, must be > 0                │
│                                                               │
│  Price Label:       ₱55.00 / sqm  (updates based on type)  │
│                                                               │
│ ───────────────────────────────────────────────────────────│
│  Information:                                               │
│  - Fixed: Same price regardless of area                    │
│  - Per sqm: Price multiplied by square meters              │
│                                                               │
│  [← Back] [Save Service]                                   │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

---

## VII. PAYMENT MANAGEMENT

### A. Payment History

**URL:** `/payments`

```
┌──────────────────────────────────────────────────────────────────┐
│  PAYMENT HISTORY                                                 │
├──────────────────────────────────────────────────────────────────┤
│  Filter: [All] [Pending] [Paid]  Filter by Date: [____] to [____]│
├──────────────────────────────────────────────────────────────────┤
│ ID │ Appointment│Customer    │ Amount     │ Method │ Status │Act│
├────┼────────────┼────────────┼────────────┼────────┼────────┼───┤
│ 1  │ #001       │ John Doe   │ ₱5,250.00  │ Cash   │ 🟢Paid │⚙️│
│ 2  │ #002       │ Jane Smith │ ₱3,800.00  │ GCash  │ 🟡Pend │⚙️│
│ 3  │ #003       │ Mike Brown │ ₱2,100.00  │ Bank   │ 🟢Paid │⚙️│
└────┴────────────┴────────────┴────────────┴────────┴────────┴───┘

Total Collected: ₱10,150.00
Pending Amount: ₱3,800.00
Collection Rate: 73%
```

---

### B. Record Payment (Only for Completed Appointments)

**URL:** `/appointments/{id}/payments/create`

```
┌───────────────────────────────────────────────────────────────┐
│               RECORD PAYMENT                                  │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  Appointment Details:                                        │
│  ─────────────────────────────────────────────────────────  │
│  Customer: John Doe                                         │
│  Appointment ID: #001                                       │
│  Status: 🟢 Completed                                        │
│  Scheduled: 2026-05-20 10:00 AM                            │
│                                                               │
│  Services Provided:                                          │
│  - Deep Cleaning (₱2,500.00)                               │
│  - Floor Polishing 50sqm (₱2,750.00)                       │
│  ─────────────────────────────────────────────────────────  │
│                                                               │
│  Payment Information:                                        │
│  ─────────────────────────────────────────────────────────  │
│                                                               │
│  Total Amount:      ₱5,250.00  (auto-calculated)          │
│                     [Adjust manually: ☐]                  │
│  Adjusted Amount:   [____________] ₱                       │
│                                                               │
│  Payment Method:    [Select Method ▼]                      │
│                     • Cash                                  │
│                     • GCash                                 │
│                     • Bank Transfer                         │
│                                                               │
│  Payment Status:    ○ Pending                               │
│                     ○ Paid                                  │
│                     * Mark as Paid if customer paid now    │
│                                                               │
│  Notes:             [____________________________]          │
│                     * Optional transaction notes          │
│                                                               │
│ ───────────────────────────────────────────────────────────│
│  [← Back] [Save Payment]                                   │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

---

## VIII. PROFILE MANAGEMENT

**URL:** `/profile`

```
┌───────────────────────────────────────────────────────────────┐
│               MY PROFILE                                      │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  Name:              [_____________________________]          │
│  Email:             [_____________________________]          │
│  Email Verified:    ✅ Yes                                   │
│                                                               │
│  [Update Profile] [Change Password] [Delete Account]        │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

---

## IX. FORM VALIDATION & ERROR HANDLING

### Validation Rules Summary

```
APPOINTMENT FORM:
├─ customer_name: Required, max 255 chars
├─ address: Required, min 5 chars
├─ area_sqm: Optional, numeric, > 0
├─ schedule_date: Required, future datetime
├─ services: At least 1 selected
├─ quantity: Required, > 0 for each service
├─ employees: At least 1 selected
└─ task: Optional for each employee

SERVICE FORM:
├─ service_name: Required, unique, max 255
├─ description: Optional, max 1000
├─ pricing_type: Required, 'fixed' or 'per_sqm'
└─ base_price: Required, decimal, > 0

EMPLOYEE FORM:
├─ name: Required, max 255
├─ phone: Required, valid phone format
├─ position: Required, max 255
└─ status: Required, 'Active' or 'Inactive'

PAYMENT FORM:
├─ amount: Required, decimal, > 0
├─ payment_method: Required, selected value
└─ payment_status: Required, 'Pending' or 'Paid'
```

### Error Messages

**Example Error Display:**

```
┌───────────────────────────────────────┐
│  ❌ Errors Found:                     │
│  • Customer name is required         │
│  • Please select at least 1 service  │
│  • Please assign at least 1 employee │
└───────────────────────────────────────┘
```

**Success Messages:**

```
┌───────────────────────────────────────┐
│  ✅ Appointment saved successfully!   │
│  Redirecting...                       │
└───────────────────────────────────────┘
```

---

## X. RESPONSIVE DESIGN

### Breakpoints

- **Desktop:** 1024px+ (Full layout)
- **Tablet:** 768px - 1023px (Stacked columns)
- **Mobile:** Below 768px (Single column, hamburger menu)

### Mobile Navigation

```
≡ MENU
├─ Dashboard
├─ Appointments
├─ Employees
├─ Services
├─ Payments
├─ Profile
└─ Logout
```

---

## XI. COLOR SCHEME & TYPOGRAPHY

### Status Colors

```
Pending     → Yellow  (#FBBF24)
In Progress → Blue    (#3B82F6)
Completed   → Green   (#10B981)
Paid        → Green   (#10B981)
Inactive    → Red     (#EF4444)
Active      → Green   (#10B981)
```

### Typography

- **Headings:** Large, bold, dark gray
- **Body Text:** Regular weight, dark gray
- **Labels:** Medium weight, darker than body
- **Buttons:** Medium weight, white on colored background

### Spacing

- Standard padding: 8px, 16px, 24px, 32px
- Standard margins: 8px, 16px, 24px, 32px
- Card spacing: 24px gap between cards

---

## XII. KEYBOARD SHORTCUTS (Optional)

```
Alt + D  → Dashboard
Alt + A  → Appointments
Alt + E  → Employees
Alt + S  → Services
Alt + P  → Payments
Alt + ? → Help/Shortcuts
```

---

## XIII. COMMON WORKFLOWS

### Workflow 1: Creating a New Appointment

1. Click "Create New Appointment" button
2. Fill in customer information
3. Select appointment date and time
4. Select required services
5. Set quantity/area for each service
6. Select employees to assign
7. Assign specific tasks to employees
8. Review total price
9. Click "Save" button
10. Confirm appointment created ✅

### Workflow 2: Recording Payment for Completed Appointment

1. Go to "Appointments" list
2. Find the completed appointment
3. Click "View" to open details
4. Scroll to Payment section
5. Click "Record Payment" button
6. Verify auto-calculated amount
7. Select payment method
8. Mark payment status
9. Click "Save Payment"
10. Confirm payment recorded ✅

### Workflow 3: Managing Employee Roster

1. Go to "Employees" section
2. Click "Add Employee"
3. Enter employee details (name, phone, position)
4. Set initial status (Active/Inactive)
5. Click "Save"
6. Employee now available for assignment ✅

---

## XIV. TROUBLESHOOTING

### Common Issues

| Issue                                            | Cause                           | Solution                                     |
| ------------------------------------------------ | ------------------------------- | -------------------------------------------- |
| Can't see "Record Payment" button                | Appointment not completed       | Change appointment status to Completed first |
| Quantity field says "Area" instead of "Quantity" | Service uses per sqm pricing    | This is normal - enter area in sqm           |
| Employee not appearing in selection              | Employee marked as Inactive     | Change employee status to Active             |
| Price calculation seems wrong                    | Custom price override is active | Check custom_price field for override        |

---

**Documentation Version:** 1.0  
**Last Updated:** May 31, 2026  
**UI Framework:** Tailwind CSS  
**Responsive:** Yes (Mobile, Tablet, Desktop)
