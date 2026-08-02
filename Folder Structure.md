# Essential Lanka (Smart Plane) - Project Folder Structure

This document outlines the complete folder and file structure for the Essential Lanka Local Manpower Service Platform.

essential-services/
│
├── .htaccess                   # URL ලස්සන කරන්න (Clean URLs) සහ Security රූල්ස්
├── .env                        # Database Passwords, API Keys හැංගිලා තියෙන ෆයිල් එක
├── 404.php                     # වැරදි ලින්ක් එකකට ගියොත් පෙන්වන "Page Not Found" පිටුව
│
├── index.php                   # වෙබ් සයිට් එකේ ප්‍රධාන Landing Page එක
├── services.php                # සේවාවන් සහ Gigs Search / Filter කරන පිටුව
├── worker-profile.php          # Worker ගේ පබ්ලික් ප්‍රොෆයිල් එක 
├── jobs-feed.php               # කස්ටමර්ස්ලා දාපු වැඩ පෝලිමට පෙන්නන පිටුව
├── messages.php                # Client ටයි Worker ටයි Live Chat කරන්න තියෙන පිටුව (Firebase)
│
├── about.php                   # About Us - ප්ලැට්ෆෝම් එකේ අරමුණ
├── contact.php                 # Contact Us - ඇඩ්මින්ව සම්බන්ධ කරගන්න ෆෝම් එක
├── faq.php                     # Q&A / Help Center - නිතර අහන ප්‍රශ්න සහ උත්තර
├── how-it-works.php            # සයිට් එක පාවිච්චි කරන හැටි පියවරෙන් පියවර
├── terms.php                   # Terms and Conditions (නීති රීති)
├── privacy.php                 # Privacy Policy (දත්ත ආරක්ෂණ ප්‍රතිපත්තිය)
│
├── database/                   # Database ගොනු
│   └── schema.sql              # පද්ධතියේ සම්පූර්ණ MySQL Database එකේ ව්‍යුහය (Tables)
│
├── lang/                       # භාෂා පරිවර්තන ෆෝල්ඩරය (Localization / i18n)
│   ├── en.php                  # ඉංග්‍රීසි වචන මාලාව
│   └── si.php                  # සිංහල වචන මාලාව
│
├── config/                     # Configuration ෆයිල්
│   ├── db.php                  # MySQL Database කනෙක්ෂන් එක
│   ├── env-loader.php          # .env ෆයිල් එක කියවන PHP කෝඩ් එක
│   └── firebase-config.js      # Firebase (Chat/Calls) සෙටින්ග්ස්
│
├── api/                        # Backend API Logic (දත්ත හුවමාරුව)
│   ├── auth.php                # Login / Register ලොජික්
│   ├── jobs.php                # Jobs Post කිරීම සහ Fetch කිරීම
│   ├── location.php            # ළඟම ඉන්න අය හොයන Haversine formula ලොජික් එක
│   ├── reviews.php             # Ratings සහ Reviews හැසිරවීම
│   ├── upload.php              # පින්තූර අප්ලෝඩ් සහ Compress කිරීම
│   ├── sms-webhook.php         # Button Phone අයට SMS යවන/ගන්න IdeaMart API එක
│   └── ussd-webhook.php        # Button Phone අයගේ #777# USSD මෙනු එක හැසිරවීම
│
├── assets/                     # පද්ධතියට අවශ්‍ය Static ෆයිල්ස්
│   ├── css/
│   │   ├── bootstrap.css       # (Optional) CSS Framework එකක් පාවිච්චි කරනවා නම්
│   │   └── style.css           # ඔයාගේ Custom CSS
│   ├── js/
│   │   ├── main.js             # Animations, UI scripts
│   │   ├── location.js         # User ගේ Live GPS Location එක ගන්න කෝඩ් එක
│   │   └── chat.js             # Firebase හරහා Real-time මැසේජ් යවන කෝඩ් එක
│   ├── images/                 # වෙබ් සයිට් එකේ Logos සහ Icons
│   └── uploads/                # පින්තූර සේව් වෙන ප්‍රධාන ෆෝල්ඩරය
│       ├── profiles/           # පරිශීලකයින්ගේ Profile Pictures
│       ├── jobs/               # කස්ටමර්ස්ලා දාන Requirement ෆොටෝස්
│       └── portfolio/          # වර්කර්ස්ලා කලින් කරපු වැඩ වල ෆොටෝස්
│
├── includes/                   # Reusable Components (පොදු කොටස්)
│   ├── header.php              # <head> ටැග් එක, Language Toggle සහ Navigation Bar එක
│   ├── footer.php              # යටින්ම තියෙන Footer එක 
│   └── alerts.php              # Success / Error මැසේජ් පෙන්නන කොටස
│
├── auth/                       # ගිණුම් වලට අදාළ පිටු
│   ├── login.php               # ලොග් වෙන පිටුව
│   ├── register.php            # අලුතින් ගිණුමක් හදන පිටුව
│   ├── forgot-password.php     # පාස්වර්ඩ් අමතක වුණොත් රීසෙට් කරන පිටුව
│   └── logout.php              # ගිණුමෙන් ඉවත් වෙන පිටුව
│
├── client/                     # පාරිභෝගිකයාගේ (Client) Portal එක
│   ├── dashboard.php           # කස්ටමර්ගේ ප්‍රධාන පිටුව (Summary)
│   ├── post-job.php            # අලුත් වැඩක් දාන පිටුව
│   ├── my-requests.php         # තමන් දාපු වැඩ වලට ආපු Bids බලන පිටුව
│   ├── active-jobs.php         # දැනට කරගෙන යන වැඩ වල Status එක
│   ├── payment.php             # Escrow/Payment කරන පිටුව (සල්ලි Hold කරලා තියන්න)
│   └── review.php              # වැඩේ ඉවර වුණාම Worker ට රේටින්ග්ස් දෙන පිටුව
│
├── worker/                     # සේවා සපයන්නාගේ (Worker) Portal එක
│   ├── dashboard.php           # ආදායම් විස්තර සහ අලුත් මැසේජ් පෙන්නන පිටුව
│   ├── my-gigs.php             # තමන්ගේ සර්විස් පැකේජ් හදන පිටුව
│   ├── find-jobs.php           # කස්ටමර්ස්ලා දාපු අලුත් වැඩ බලලා Bids යවන පිටුව
│   ├── active-jobs.php         # දැනට භාරගෙන කරන වැඩ වල Status එක අප්ඩේට් කරන්න
│   ├── portfolio.php           # කලින් කරපු වැඩ වල පින්තූර දාන පිටුව
│   └── verification.php        # NIC/Police clearance අප්ලෝඩ් කරන පිටුව
│
└── admin/                      # පද්ධති පාලකගේ (Super Admin) Portal එක
    ├── dashboard.php           # මුළු සිස්ටම් එකේම Analytics (Charts, Users count)
    ├── verify-users.php        # Workers ලා අප්ලෝඩ් කරපු NIC බලලා Approve/Reject කරන්න
    ├── manage-categories.php   # සර්විස් වර්ග (Categories) ඇඩ්/ඩිලීට් කරන පිටුව
    ├── disputes.php            # කස්ටමර් සහ වර්කර් අතර ප්‍රශ්න විසඳන Ticket පද්ධතිය
    └── reports.php             # ආදායම් සහ සිස්ටම් රිපෝට්ස් (PDF/Excel) ගන්න පිටුව
