# Project Overview

- **Project Name:** AIC Bondeni Website  
- **Purpose:** Bridge the gap between church leadership and congregants, while sharing information about the church with members and visitors.  
- **Target Users:** Church congregants, visitors, pastors, and church leaders.  
- **Primary Actions:**  
  - Learn about the church  
  - Join or participate in church activities  
  - Watch or listen to sermons  
  - Submit prayer requests  
  - Give offerings and donations  

---

# Site Structure & Navigation

## About Us

The About section will introduce visitors to the church and its leadership.

### Pages / Sections
- `/about/our-story`  
  Contains the history and background of the church.

- `/about/what-we-believe`  
  Contains the church mission, vision, values, and statement of faith.

- `/about/leaders`  
  Displays church leaders and pastors with their profiles.

### Features
- Professional staff and pastor photos  
- Leader biographies and descriptions  
- Contact information  
- Church information and mission details  

---

## Ministries / Get Involved

This section will showcase all church ministries and ways congregants can participate.

### Ministries
- Youth Ministry  
- Children Ministry (Sunday School)  
- Men Fellowship  
- Women Fellowship  
- Battalion  
- Cadet  
- Nyota  
- Missions & Evangelism  
- Ushering  

Each ministry will have its own dedicated page:

```txt
/ministries/[ministry-name]
```

Each page may include:
- Ministry description  
- Leadership information  
- Meeting schedules  
- Activities and events  
- Images or gallery  

---

## Sermons

Displays all sermons uploaded by administrators.

### Features
- Sermon listing page  
- Individual sermon pages  

Example route:

```txt
/sermons/[sermon-title]
```

Each sermon page may contain:
- Sermon title  
- Description  
- Pastor/Preacher  
- Audio or video content  
- Sermon date  

---

## Pastors

Displays all pastors and church leaders.

### Features
- Pastor cards with profile images  
- Individual pastor pages  

Example route:

```txt
/pastors/[pastor-name]
```

Each pastor page will contain:
- Biography and description  
- Photos  
- Social media links  
- Related sermons  

---

## Events

Displays upcoming and past church events.

### Features
- Event posters and banners  
- Event descriptions and schedules  
- Data managed by administrators through the admin dashboard  

---

## Prayer Request Page

Allows visitors and members to send prayer requests to pastors.

### Features
- Guest submissions  
- Signed-in user submissions  
- Requests sent directly to the pastors dashboard  

---

## Testimony Submission Page

Allows users to submit testimonies to the pastors portal.

### Features
- Testimony form  
- Registered user submissions  
- Pastor/admin review system  

---

## Volunteer Sign-Up System

### Route
```txt
/ministries/volunteer
```

Allows believers to volunteer in different church activities and ministries.

### Features
- Volunteer registration form  
- Ministry selection  
- Contact details submission  

---

## Giving

Allows congregants to support the church financially.

### Features
- Display giving methods  
- Online payment integration  
- Possible support for:
  - Stripe  
  - M-Pesa STK Push  

---

# Homepage Framework

## Hero Section
- High-quality background image inside a rounded container  
- Church name and introduction text  
- Call-to-action button  

---

## Floating Quick Links Tab

A floating section overlapping the hero area containing quick links:

- Request Prayer  
- Get Directions  
- Request Information  

---

## Welcome Message Section
- Welcome message from the pastor  
- Pastor image/photo  

---

## Testimonies Section
- Testimony cards with images  
- Testimonies displayed in sequence or slider format  

---

## Events Preview Section
- Introduction to church events  
- Link to the main events page  
- Display up to 3 featured events in a grid layout  

---

## Latest Sermons Section
- Display the latest 3 sermons  
- Link to the full sermons page  

---

## Services Schedule Section
Displays the church service schedule for the entire week.

---

## Layout Rules
- Card-based sections should display a maximum of 3 cards per row.  

---

## Footer Structure

### Section 1
- Church logo  
- Church name  

### Section 2
- Bible verse: Matthew 21:13  
- Social media links  
- Church contact details  

### Section 3
- Centered copyright information  

---

# Key Functional Features

- Mobile responsive design  
- Admin dashboard  
- Pastor portal  
- Authentication and user state management (guest/authenticated users)  
- Dynamic content management for sermons, events, testimonies, and ministries  
- Secure online giving support  