# BASTOT `Backend`

**Bastot** is a web platform focused on helping basketball players improve their skills, connect with the community, and advance their careers. The platform also supports court management and scouting functionalities.

## Project Goals
* Provide community support for basketball players
* Enable court reservations and management
* Offer structured training resources
* Facilitate player discovery for scholarships and sponsorships
---
## Role Management:
### *REGULAR USER (PLAYER)*

Basketball:
- My Teams
- My Stats
- Highlights
  Training:
- Workout Plans
- Skill Developments
  
Community:
- Events & Tournaments
- Teammates
- My Communities

Careers:
- Scholarships
- Sponsorships
- Boosts Received

### *COURT OWNER*

Courts:
- My Courts
- Reservations
- Users History
  
Community:
- Hosted Events
- Tournament Management
- Boost Players

### *SCOUTER / SPONSOR PROVIDER*

Scouting:
- Scouting Report
- Player Search
- Boost Players

Careers:
- Manage Scholarships
- Manage Sponsorships
- Incoming Applications

---

## Core Models
### *Users and Roles*
1. `User` – base user model
2. `Role` – defines user types: Player, Court Owner, Scouter
3. `UserRole` – pivot table if a user can have multiple roles
---
### *Basketball (Player)*
4. `Team`
5. `TeamUser` – pivot for team memberships
6. `Stat` – player statistics
7. `Highlight` – video or image uploads of gameplay
---
### *Training*
8. `WorkoutPlan`
9. `SkillDevelopment`
10. `TrainingVideo` (if you separate videos from plans)
11. `Drill`
---
### *Community*
12. `Community`
13. `CommunityUser` – pivot table for community memberships
14. `Event`
15. `Tournament`
16. `TeammateRequest` – optional: if players can send requests
---
### *Court Management*
17. `Court`
18. `Reservation`
19. `CourtReview` – optional for rating/favorites
---
### *Careers*
20. `Scholarship`
21. `Sponsorship`
22. `Application` – tracks user applications
23. `ScoutingReport`
24. `Boost` – tracks which user was boosted, by whom, and why
---
### Optional Utility Models:
25. `Notification`
26. `Media` – if you store uploads (videos/images) centrally
27. `Location` – for court/event geolocation

---

## Getting Started
1. Clone the repository:
```bash
git clone https://github.com/fadhel-rizanda/bastot-backend.git
```
2. Install dependencies:
```bash
composer install
```
3. Create a copy of the `.env.example` file and rename it to `.env`:
```bash
cp .env.example .env
```
to be discussed...
https://app.diagrams.net/#G1VCXwEQlDYPoBscY-hq9u9IHJlXEPbYM-#%7B%22pageId%22%3A%223gLzajpsw-CqP6qquiOM%22%7D