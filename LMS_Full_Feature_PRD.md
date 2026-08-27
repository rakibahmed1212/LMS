# Subscription-Based Online Tuition Platform (LMS) — Product Requirement Document (PRD)

**Version:** 1 (client requirements + standard LMS feature additions)

---

## 1. Purpose & Scope

এটি একটি **subscription-based online tuition platform** — সাধারণ কোর্স-বিক্রির LMS নয়, বরং পরিবার/অভিভাবক-কেন্দ্রিক একটি মডেল। এখানে একজন Parent একাধিক Student (সন্তান) manage করবে, এবং প্রতিটা Student আলাদাভাবে একাধিক subject subscribe করবে (যেমন: Year 3 Maths + Year 1 English)।

**Core business rule:** কোনো কোর্স সরাসরি "private" বা "public" — এই দুইভাবে চিহ্নিত হবে না। বরং access পুরোপুরি নির্ভর করবে subscription-এর উপর:

```
User → Subscription → Course → Access
```

অর্থাৎ, subscription active থাকা অবস্থায় access পাওয়া যাবে; expire হয়ে গেলে access বন্ধ হয়ে যাবে — তবে historical progress record মুছে যাবে না, সংরক্ষিত থাকবে।

---

## 2. Tech Stack (আগের সিদ্ধান্ত অনুযায়ী)

| Layer | Technology |
|---|---|
| Backend | Laravel (PHP 8.3/8.4) — **Monolith**, microservice নয় |
| Frontend Bridge | Inertia.js |
| Frontend UI | React |
| Database | MySQL |
| Auth | Laravel Breeze/Sanctum (session-based) |
| Video Hosting | 3rd-party streaming (Mux/Bunny Stream/Vimeo Pro/Cloudflare Stream) — **সরাসরি নিজের সার্ভারে video upload নয়** |
| Payment | Recurring subscription gateway (Stripe/SSLCommerz recurring, bKash যদি recurring সাপোর্ট করে) |
| Queue | Laravel Queue (Redis) — notification, subscription renewal check, certificate generation |
| Mobile | v1: PWA, Phase 3: React Native (separate API layer) |

---

## 3. Core Data Hierarchy

কনটেন্ট স্ট্রাকচারটি এমনভাবে ডিজাইন করা হবে যাতে প্রতি একাডেমিক বছরে নতুন করে তৈরি না করে reuse করা যায়:

```
Class/Year
 └─ Subject
     └─ Course
         └─ Module/Topic
             └─ Lesson
                 └─ Video / Worksheet / Quiz
```

অন্যদিকে, access ও progress ট্র্যাকিং সম্পূর্ণভাবে Student-কেন্দ্রিক:

```
Parent/User
 └─ Student (একাধিক সন্তান)
     ├─ Subscription → Payment → Course Access
     └─ Progress → Assessment Results
```

> **গুরুত্বপূর্ণ:** Class/Year structure প্রতি একাডেমিক বছরে পুনরায় তৈরি করতে হবে না — একই template reuse হবে, শুধু নতুন session/year cycle-এ student assign করা হবে।

---

## 4. User Roles

| Role | Permission |
|---|---|
| Super Admin | সব কিছু — students, subscriptions, revenue, content, staff |
| Tutor | নিজের lesson তৈরি, video upload, quiz তৈরি, assigned student দেখা |
| Content Manager | শুধু educational material upload/edit (financial/admin access নেই) |
| Parent (Primary Account Holder) | একাধিক Student manage, subscription কেনা/বাতিল, payment history দেখা |
| Student | নিজের subscribed course-এ access, progress দেখা, quiz/assignment দেওয়া |

---

## 5. Feature Requirements

### 5.1 Parent & Student Accounts

- Parent-level primary account: email/phone + password login
- **Unique Student ID** auto-generate (format: `STU-YYYY-XXXXX`)
- এক Parent account থেকে একাধিক Student (সন্তান) add ও manage করা যাবে
- Parent dashboard-এ সব child এবং তাদের সব subscribed course এক জায়গায় দেখা যাবে
- Parent সহজে এক child থেকে আরেক child-এ switch করে individual progress দেখতে পারবে
- Profile update, payment/subscription history view

### 5.2 Subscription System (Core)

- **Subscription granularity:** প্রতি Student-এর জন্য প্রতি Subject আলাদাভাবে subscribe করা যাবে (যেমন Year 3 Maths এবং Year 3 English সম্পূর্ণ আলাদা subscription)
- Billing cycle: Monthly / Annual
- Subject/Class ভেদে আলাদা pricing
- Bundle pricing (e.g., Maths + English একসাথে নিলে discounted rate)
- Free trial period (configurable দিন সংখ্যা)
- Coupon/discount code
- **Automatic recurring payment** (card-on-file, auto-charge)
- Cancellation, renewal, upgrade/downgrade
- Expiry date tracking + grace period
- Payment receipt/invoice auto-generate
- **Dunning flow:** Payment failed হলে status `past_due`-এ যাবে → access restrict/block হবে → reminder email/SMS যাবে → retry করা হবে → শেষ পর্যন্ত unresolved থাকলে subscription cancel হবে

### 5.3 Class/Year & Curriculum Structure

- Year/Class → Subject → Course → Module/Topic → Lesson — এই nested tree structure অনুসরণ করা হবে
- একই structure template প্রতি একাডেমিক year reuse করা যাবে (copy/clone feature দিয়ে)
- Admin/Tutor থেকে drag-drop-এ reorder করা যাবে

### 5.4 Video Lessons

- HD video streaming — নিজের সার্ভারে হোস্ট না করে 3rd-party streaming service ব্যবহার করা হবে, যাতে bandwidth ও piracy risk এড়ানো যায়
- Video chapters/markers
- Resume playback — student যেখানে pause করেছিল, সেখান থেকেই আবার শুরু করতে পারবে
- Watch percentage tracking (প্রতি student, প্রতি video-র জন্য আলাদাভাবে)
- Manual/auto "mark as completed"
- Downloadable worksheet/PDF attachment
- Subtitle support (optional)
- Video thumbnail
- Access control/DRM-lite: signed URL, domain-lock, download prevention
- Video replace/update হলেও পুরনো student progress data যেন নষ্ট না হয় — তাই video ID-based tracking ব্যবহার হবে, timestamp-based নয়

### 5.5 Student Progress Tracking

প্রতি student-এর জন্য নিচের বিষয়গুলো ট্র্যাক করা হবে:

- Subscribed courses list
- Lessons viewed / completed
- Video watch %
- Quiz results
- Assignment results
- Overall course progress %
- Last login, last lesson accessed
- Time spent learning
- Certificates earned

**Progress view উদাহরণ:**

| Course | Progress | Last Activity |
|---|---|---|
| Year 3 Maths | 72% | 7 Aug |
| Year 3 English | 45% | 6 Aug |
| Year 3 Science | 91% | 8 Aug |

### 5.6 Quiz & Assessment

- Question types: MCQ, True/False, Short answer
- Auto-marking (objective question types-এর জন্য)
- Randomized question order, question bank থেকে প্রশ্ন নেওয়া
- Timed tests
- Practice test ও graded test আলাদাভাবে রাখা যাবে (End-of-topic, End-of-year)
- Result সরাসরি student progress record-এর সাথে link থাকবে

### 5.7 Parent Dashboard

- Multi-child switch view
- প্রতি child-এর জন্য: subscribed subjects, progress, upcoming renewal, payment history
- একই dashboard থেকে যেকোনো child-এর জন্য নতুন subscription যোগ করা যাবে

### 5.8 Admin Dashboard

**Students:**
- Student ID, name, email, address, school, class/year দিয়ে সার্চ করা যাবে
- Active/inactive status, subscription status দেখা যাবে

**Subscriptions:**
- Active / expired / cancelled তালিকা
- আসন্ন renewal-এর তালিকা
- Revenue report

**Learning Analytics:**
- সবচেয়ে বেশি দেখা কোর্স
- Progress-এ পিছিয়ে থাকা student চিহ্নিত করা (low activity/progress flag)
- গড় quiz score
- Course completion rate
- Student activity log

### 5.9 Tutor / Staff Roles

- Super Admin: full access
- Tutor: lesson তৈরি, video upload, quiz তৈরি — তবে শুধু নিজের assigned student-দের দেখতে পারবে
- Content Manager: শুধু material upload/edit করতে পারবে (financial/admin access থাকবে না)

### 5.10 Search & Navigation

- Hierarchical navigation: Year → Subject → Topic → Lesson
- Lesson, worksheet, quiz — সব জায়গায় free-text search (যেমন "fractions" লিখে সার্চ করলে relevant সব কনটেন্ট আসবে)

### 5.11 Mobile Strategy

- **v1:** Mobile-first responsive + PWA (installable, push notification)
- **Phase 3:** Native app (React Native), যার জন্য আলাদা stateless REST API layer বানাতে হবে

### 5.12 Notifications

নিচের ঘটনাগুলোতে automated notification যাবে:
- Welcome email
- Subscription confirmation
- Payment receipt
- Renewal reminder (expiry-র আগে)
- Expiry warning / payment failed alert
- New lesson published
- Assignment reminder
- Quiz result
- Certificate issued

Phase 2-এ PWA-র মাধ্যমে push notification যোগ হবে।

### 5.13 Certificates

- Course সম্পূর্ণ হলে auto-generate হবে (যেমন: "Certificate of Completion — Year 4 Mathematics — Student ID: STU-2026-00125")
- Template builder এবং QR/verification code সুবিধা থাকবে (generic LMS PRD-তে যেভাবে বর্ণনা করা হয়েছিল, সেই একই কাঠামোয়)

### 5.14 Security

- Secure authentication (hashing, rate-limited login, 2FA optional)
- Role-based permissions (granular)
- Encrypted connections (HTTPS/TLS)
- Secure signed video access (hotlinking/sharing প্রতিরোধ)
- Account-sharing প্রতিরোধ (device/session limit — optional)
- PCI-compliant payment handling — raw card data কখনো নিজেদের সার্ভারে না রেখে gateway tokenization ব্যবহার করা হবে
- Automatic backups
- Audit logs (admin/tutor-এর কার্যকলাপ)
- **Child/student data protection:** যেহেতু এই platform-এ minor-দের ডেটা থাকবে, তাই GDPR/child-data protection নীতি মেনে চলা আবশ্যক (data minimization, parental consent, restricted data sharing)

### 5.15 Payment & Access Control Example

| Plan | Monthly | Annual |
|---|---|---|
| Year 3 Maths | £15 | £150 |
| Year 3 English | £15 | £150 |
| Year 3 Maths + English (bundle) | £25 | £250 |

**Access control logic — উদাহরণ:**

```
Student A → Subscription: Year 3 Maths — ACTIVE → Access granted
Student A → Year 3 Science — NOT SUBSCRIBED → Access denied
```

Subscription expire হয়ে গেলে access স্বয়ংক্রিয়ভাবে বন্ধ (revoke) হয়ে যাবে, তবে historical progress ও certificate data সংরক্ষিত থাকবে।

### 5.16 Assignment System *(Standard LMS addition — client doc-এ ছিল না)*

Client-এর ডকুমেন্টে শুধু Quiz-এর কথা উল্লেখ ছিল। কিন্তু একটা সম্পূর্ণ tuition LMS-এ homework/assignment-ও আলাদাভাবে থাকা দরকার:

- File upload submission (PDF/image/doc) — homework বা হাতে লেখা answer scan করে জমা দেওয়ার জন্য উপযোগী
- Deadline + late submission flag
- Tutor থেকে feedback/comment এবং score
- Resubmission allow/disallow (tutor নিয়ন্ত্রণ করবে)

### 5.17 Gradebook & Weighted Result *(Standard LMS addition)*

- প্রতি student-এর Quiz ও Assignment score একসাথে (per subject) দেখা যাবে
- Weight configurable থাকবে (যেমন Quiz 60% + Assignment 40%) — tutor/admin এটি সেট করতে পারবে
- Term/topic-wise result summary — এটাই parent-কে progress report হিসেবে দেখানো যাবে
- এই ফিচারটি section 5.5-এর Progress Tracking-এর সাথেই যুক্ত থাকবে, আলাদা মডিউল হিসেবে নয়

### 5.18 Discussion / Doubt-Solving *(Standard LMS addition)*

Tuition platform-এর ক্ষেত্রে এই ফিচারটি বিশেষভাবে গুরুত্বপূর্ণ — পড়া বুঝতে না পারলে student সরাসরি প্রশ্ন করতে পারবে:

- প্রতি lesson-এ Q&A/comment thread
- Tutor উত্তর দিলে notification যাবে
- Parent moderation visibility (optional — শিশুদের নিরাপত্তার জন্য)

### 5.19 Live Class Integration *(Standard LMS addition)*

- Recorded video-র পাশাপাশি live doubt-clearing session/class শিডিউল করার সুবিধা থাকবে
- Zoom/Google Meet/Jitsi embed + calendar scheduling
- শুধুমাত্র subscription-active student-রাই join করতে পারবে (একই access control logic প্রযোজ্য হবে)

### 5.20 Tutor Earnings *(Standard LMS addition)*

- একাধিক Tutor থাকলে, তাদের assigned subject/student অনুযায়ী payout ট্র্যাক করা যাবে
- Revenue-share বা fixed-salary — যেকোনো মডেল configurable রাখা হবে (v1-এ শুধু একটা simple report থাকলেই যথেষ্ট, payout automation Phase 2-এ যোগ হবে)

### 5.21 Multi-language Support *(Standard LMS addition)*

- UI-তে Bengali/English toggle থাকবে (client-এর target audience অনুযায়ী প্রয়োজনে পরিবর্তনযোগ্য)

> **Note:** উপরের 5.16–5.21 নম্বর ফিচারগুলো client-এর মূল ডকুমেন্টে সরাসরি উল্লেখ ছিল না — একটা সম্পূর্ণ ও প্রতিযোগিতামূলক LMS-এ সাধারণত এগুলো প্রয়োজন হয় বলেই যোগ করা হয়েছে। Client review করে confirm করে দিলে ভালো হয়, কোনগুলো v1-এ রাখা দরকার আর কোনগুলো বাদ দেওয়া যায়।

---

## 6. High-Level Data Entities (Revised)

```
Parent (id, name, email, phone, password)
Student (id, parent_id, student_code[unique], name, dob, school, class_year_id)
ClassYear (id, name)               -- e.g. "Year 3"
Subject (id, class_year_id, name)  -- e.g. "Mathematics"
Course (id, subject_id, title)
Module (id, course_id, order)
Lesson (id, module_id, video_url_ref, order)
Worksheet (id, lesson_id, file_url)
Quiz (id, lesson_id, settings_json)
Question (id, quiz_id, type, bank_tag)

SubscriptionPlan (id, subject_id, billing_cycle, price)
Subscription (id, student_id, plan_id, status, start_date, expiry_date)
Payment (id, subscription_id, gateway, amount, status, invoice_no)

Progress (id, student_id, lesson_id, watch_percent, completed_at)
QuizAttempt (id, quiz_id, student_id, score, status)
Assignment (id, lesson_id, deadline, max_score)
AssignmentSubmission (id, assignment_id, student_id, file_url, score, feedback, submitted_at)
Gradebook (id, student_id, subject_id, quiz_weight, assignment_weight, final_score)
DiscussionThread (id, lesson_id, student_id, message, parent_thread_id)
LiveClass (id, subject_id, tutor_id, meeting_url, scheduled_at)
Certificate (id, student_id, course_id, cert_code, issued_at)

TutorAssignment (id, tutor_id, course_id)   -- role-based scoping
```

---

## 7. Non-Functional Requirements

| Category | Requirement |
|---|---|
| Performance | Video streaming CDN via 3rd-party (Mux/Bunny/Cloudflare Stream), page load < 2s |
| Scalability | Monolith + horizontal scaling, queue workers স্বাধীনভাবে scale করা যাবে |
| Security | Child-data protection compliance, PCI-tokenized payments, RBAC |
| Availability | 99.5% uptime target |
| Data Retention | Subscription cancel হওয়ার পরেও progress/certificate data সংরক্ষিত থাকবে |

---

## 8. Phased Rollout Plan

| Phase | Scope |
|---|---|
| Phase 1 (MVP) | Parent/Student accounts, Class/Subject/Course structure, Video lessons + progress tracking, Subscription + recurring payment, Basic quiz, Assignment submission, Gradebook, Admin dashboard, PWA |
| Phase 2 | Coupons/free trial, Certificates, Notifications (full set), Discussion/Q&A, Live class integration, Advanced analytics ("students falling behind"), Content Manager role, Tutor earnings report |
| Phase 3 | Native mobile app (React Native + REST API), Push notification, DRM-hardened video, Multi-language toggle, AI-based "at-risk student" prediction |

---

## 9. Open Questions

1. Video hosting provider হিসেবে কোনটা পছন্দ — Bunny Stream (কম খরচ) নাকি Mux/Cloudflare Stream (বেশি feature)?
2. Recurring payment-এর জন্য bKash/Nagad-এ subscription/tokenized billing সাপোর্ট আছে কিনা যাচাই করা দরকার — না থাকলে Stripe অথবা manual renewal reminder + relink flow ব্যবহার করতে হবে
3. একই Student কি একাধিক Year-এ থাকতে পারবে (যেমন mid-year progression/promotion flow)?
4. Device/session limit দিয়ে account-sharing prevention v1-এই দরকার, নাকি Phase 2-এ রাখলে চলবে?

---

*পরবর্তী ধাপ: এই PRD approve হলে Subscription + Access Control module-এর detailed database schema ও Eloquent relationship সবার আগে বানানো হবে, যেহেতু এটাই পুরো platform-এর সবচেয়ে critical business logic।*
