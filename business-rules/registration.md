# Module: Registration (Shared)

## Business Rules

1. A registration year is determined by the global settings configuration.
   Source: `class.globalSettings.php:21-30`

2. Australian schools use a different registration year calculation: if the current month is after August, the registration year is decremented by one.
   Source: `class.globalSettings.php:23-26`

3. MyShliach (school_id 61) and Anash Kinder (school_id 269) have special handling for registration year dates.
   Source: `class.globalSettings.php:27-29`

4. A user can be unregistered only by a super administrator.
   Source: `registration/unregister.php:5-8`

5. Unregistration is transactional — if any step fails (deleting registration records, charges, or chidon records), the entire operation rolls back and the user remains registered.
   Source: `registration/unregister.php:37-84`

6. Each user can be registered for a given year only once.
   Source: `SQLdump/mashpiadb_user_registration.sql:34` (UNIQUE on user_id + year)

7. A registration charge can be marked as refunded with a reason recorded for audit purposes.
   Source: `SQLdump/mashpiadb_registration_charges.sql:38-39`

8. Registration charges are tracked by type (THE, LDE, RRFAM, HACH, THAKUSA, THAKCAN, THAKINT, THMSUSA, THMSCAN, THMSINT, etc.), amount, discount, year, and payment/refund status.
   Source: `SQLdump/mashpiadb_registration_charges.sql:25-45`

9. A school's registration type (1=tuition, 2=grants, 3=other) and whether it supports chidon registration must be configured by administrators.
   Source: `registration_4.php:80-97`

10. Only school administrators can view and manage user registrations for their assigned schools; super administrators can view all schools.
    Source: `registration/getRegistration.php:5-29`

11. Payment information is sent to Authorize.Net for credit card processing, with transaction details tracked for reference.
    Source: `api/registration/user_registration.php:150-282`

12. Installment payments (subscriptions) can be created for advance registration amounts and are deducted from the total charge.
    Source: `api/registration/user_registration.php:202-233`

13. Multiple registration items (codes) can be charged in a single transaction; codes are separated by colons.
    Source: `api/registration/user_registration.php:401-425`

14. A registration payment profile can be reused from a previous transaction or created new if the parent provides card details.
    Source: `api/registration/user_registration.php:188-200`

15. Shipping fees are charged for eligible schools (MyShliach and Anash Kinder) based on shipping zone (USA, Canada, International) with a base rate and a per-child increment.
    Source: `api/registration/user_registration.php:84-147`

16. A family can only be charged once for shipping per year across all children at eligible schools.
    Source: `api/registration/user_registration.php:62-81`

17. Anash Kinder shipping rates: Zone 1 = $67 + $20/child, Zone 2 = $100 + $20/child, Zone 3 = $167 + $20/child.
    Source: `api/registration/user_registration.php:109-122`

18. MyShliach shipping rates: USA = $35, Canada = $40, International = $45.
    Source: `api/registration/user_registration.php:131-141`

19. A confirmation email is sent to parents after successful registration with itemized details of enrolled programs.
    Source: `api/registration/user_registration.php:511-514`

20. Registration confirmation requires a parent email confirmation for tuition-type schools where the parent has not yet confirmed information.
    Source: `api/models/Soldier.php:722-735`

---

# Module: Chayolei Registration

## Business Rules

1. A soldier is eligible for Chayolei registration if they are registered with a school that has the chayolei flag enabled.
   Source: `api/models/Soldier.php:653-659`

2. For tuition-type schools, Chayolei registration can be completed by the parent without payment; registration is recorded even if the amount is zero.
   Source: `api/models/Soldier.php:807-818, 895-917`

3. A soldier cannot be registered unless at least one of the following is true: the amount paid is greater than zero, or a discount is applied.
   Source: `api/models/Soldier.php:807`

4. When a soldier is registered for a future year, they must also be registered for the current year.
   Source: `api/models/Soldier.php:827-839`

5. Applied discounts are marked as used (used = NOW()) in the discounts table at the time of registration.
   Source: `api/models/Soldier.php:857-865`

6. Soldier registration charges are recorded with type='THE' for standard registration, 'chayolei-lite' for lite, or 'ckids' for CKids registration.
   Source: `api/models/Soldier.php:843-846`

7. Registration charges default to the Chayolei year (from global settings) unless the charge type contains 'THE', in which case the registration year is used.
   Source: `api/models/Soldier.php:523-526`

8. Upon Chayolei registration, the soldier's `user_registered` timestamp is set (if not already set) and a start date (JD format) is recorded.
   Source: `api/models/Soldier.php:869-873`

9. Chayolei-lite registration sets the `lite_edition` flag and does NOT grant hachayols or medals/ranks.
   Source: `api/models/Soldier.php:875-881`

10. Standard Chayolei registration (type='THE') grants hachayols and medals/ranks; CKids registration (type='ckids') does NOT grant these.
    Source: `api/models/Soldier.php:876-881`

11. Upon Chayolei registration, a rank is generated and the soldier is enrolled in campaigns and birthday missions if this is their first registration.
    Source: `api/models/Soldier.php:883-888`

12. Hachayol is assigned to one child per family; preference is given to children in grades 1–5, otherwise to the oldest child.
    Source: `api/models/Soldier.php:602-612`

13. Australian schools are excluded from Hachayol assignment.
    Source: `api/models/Soldier.php:589, 594-595`

14. If a child has no parent account (no admin_auths entry), Hachayol is not assigned unless the child is the only one in the family.
    Source: `api/models/Soldier.php:555-562`

15. Anash Kinder (school_id 269) and MyShliach (school_id 61) receive extra charges for their respective programs (MYSLDS-10, AKLDS-10, AKLDBC-20).
    Source: `api/registration/user_registration.php:401-425`

---

# Module: Chidon Registration

## Business Rules

1. A soldier is eligible for Chidon registration if: (1) they are in grade 3–8, (2) their school is flagged as a chidon school, (3) the user flag `chidon=1`, and (4) the school is not in the exclusion list (school IDs 482, 544, 583).
   Source: `api/models/Soldier.php:665-670`

2. Monsey schools (IDs 49, 192) are always eligible for Chidon registration regardless of the school's general registration status.
   Source: `api/models/Soldier.php:667, 694-697`

3. Chidon registration will be closed globally after February 12, 2027 (Eastern Time).
   Source: `api/models/Soldier.php:754-763`

4. A soldier can edit their Chidon registration only if they are already registered (th_chidon_id exists).
   Source: `api/models/Soldier.php:670`

5. Soldiers who have never been registered for Chidon before are flagged as `new_to_chidon=1`.
   Source: `api/models/Soldier.php:711-718`

6. Chidon registration requires at least an amount value (trans_id and amount cannot both be null).
   Source: `api/models/Soldier.php:971`

7. A free Chidon registration (paid = $0) marks the child as enrolled with a timestamp but no payment approval.
   Source: `mobile/chidon/ajax/enroll_shabbaton.php:14-19`

8. If a soldier is not already in Chidon, a Chidon registration charge (type='LDE') is automatically created.
   Source: `api/models/Soldier.php:1011-1013`

9. Book assignment for Chidon is based on grade: grade 4 = book 1, grade 5 = book 2, grade 6 = book 3, grade 7 = book 4, grade 8 = book 1.
   Source: `chidon_reg_post.php:53-69`

10. Upon Chidon registration, a parent ID is required; if not provided, it is looked up from admin_auths with role_id=1.
    Source: `api/models/Soldier.php:973-984`

11. If a soldier's recruiter changes during Chidon registration, the `newRecruit` flag is set to trigger a recruitment notification email.
    Source: `api/models/Soldier.php:994`

12. When a soldier is recruited for Chidon, an email is sent to the recruiter with the name of the newly recruited child.
    Source: `api/registration/user_registration.php:437-442`

13. Soldiers can register for Chidon with a recruiter, identified by the recruiting soldier's user_id.
    Source: `api/registration/user_registration.php:343-344, 370-375`

14. Chidon registration fields include: sweater size, book (1–5), yarmulka flag, name preference, recruitment status, poll responses, comments, test_type, and parent ID.
    Source: `api/models/Soldier.php:965-1044`

15. Chidon test tracks available: maven, pro, expert, trophy, genius.
    Source: `api/models/Soldier.php:1004, 1041`

16. Only soldiers eligible for KHK (Kol HaTorah) track will be offered that option; eligibility is determined by KHK::enrollmentEligibility().
    Source: `api/models/Soldier.php:707-708`

17. Chidon registration can include KHK registration via an optional khk_reg flag.
    Source: `api/models/Soldier.php:1136-1142`

18. Chidon registration can include multiple prize selections, which replace any previously selected prizes.
    Source: `api/models/Soldier.php:1151-1163`

19. Chidon registration can include book purchase information: location (parent_account or store), store name, store city, and version.
    Source: `api/models/Soldier.php:1077-1133`

20. A Chidon Shabbaton registration can include sponsorship; the sponsorship amount is calculated as total − baseAmount and tracked separately.
    Source: `mobile/chidon/ajax/processPayment.php:30-60`

21. A Chidon payment is recorded in th_chidon with paid amount, date_paid, and Authorize.Net confirmation approval.
    Source: `mobile/chidon/ajax/processPayment.php:35-40`

22. A confirmation email is sent to parents and to chidon@tzivoshashem.org after successful Chidon payment, with all child details included.
    Source: `mobile/chidon/ajax/processPayment.php:63-134`

23. Anash Kinder (school_id 269) receives a separate special notification email after a student's Chidon registration.
    Source: `api/registration/user_registration.php:335-338, 445-447`

---

## Open Questions

- `class.globalSettings.php` — An `earlyBird()` method returns Sept 17, 2026 as a cutoff, but the pricing logic tied to early bird status was not found. Is there an early bird discount and what does it apply to?
- `SQLdump/` — Both `chidon_reg` and `th_chidon` tables appear to store Chidon registration data with different schemas. Is `chidon_reg` a deprecated table or does it serve a separate purpose?
- `registration_4.php` vs `registration_ckids.php` — Two separate registration flows exist with different school type handling. What determines which flow a school follows?
- `mashpiadb_registration_charges.sql` — The `approved` field appears to store Authorize.Net response codes. Is there a business rule for when approval is required vs. when registration can proceed without it?
- `api/models/Soldier.php` — The exact business difference between tuition schools (reg_type=1) and grant/other schools for Chayolei registration is not fully expressed in the code. What privileges or constraints differ?
- `api/models/Soldier.php` — The original criteria for which schools should have Hachayol assigned is not documented in code. Is there a configuration flag or a list?
- Multiple files — Schools 61, 269, 49, 192, 482, 544, 583 appear as hardcoded special cases across multiple files. Are there additional schools with special handling not yet identified?
- `api/models/Soldier.php` — The user-facing differences between Chayolei-lite and standard Chayolei registration (beyond medals/ranks/hachayols) are not documented in code.
- `api/registration/user_registration.php` — Codes RRFAM, RRYSD, RRYDA, RRHVN appear to represent installment/advance registration fees. What are the amounts and conditions for each?
- `mashpiadb_registration_charges.sql` — No refund processing logic was found; the refund_reason field suggests manual entry. Is refund processing handled entirely outside this system?
