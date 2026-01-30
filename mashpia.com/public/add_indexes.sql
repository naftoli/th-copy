-- ============================================================================
-- Database Indexes for Missions and Duch System Performance
-- ============================================================================
-- Based on analysis of actual queries in:
-- - mission_report/classes/missions.php
-- - classes/user.php
-- - classes/user_track.php
-- ============================================================================

-- ============================================================================
-- PRIORITY 1: CRITICAL INDEXES
-- ============================================================================

-- Index for user_tracks: WHERE user_id = ? AND enrolled = 1
-- Query: SELECT ut.* FROM user_tracks WHERE ut.user_id = ? AND ut.enrolled = 1
-- Used in: classes/user.php::get_user_tracks()
CREATE INDEX idx_user_tracks_enrolled ON user_tracks (user_id, enrolled, subject_id);

-- Index for rank_marks: MAX(rank_ord) WHERE user_id = ?
-- Query: SELECT MAX(rm.rank_ord) FROM rank_marks WHERE rm.user_id = ?
-- Used in: classes/user.php::get_rank()
CREATE INDEX idx_rank_marks_user_max ON rank_marks (user_id, rank_ord DESC);

-- Index for date_tasks_missions: Complex WHERE clause
-- Query: SELECT * FROM date_tasks_missions WHERE lang_id=? AND school_type_id=? 
--        AND subject_id=? AND level=? AND track_id=? AND start_date>=? AND end_date<=?
-- Used in: classes/user_track.php::get_date_tasks_missions()
CREATE INDEX idx_dtm_complete ON date_tasks_missions (
  school_type_id, 
  subject_id, 
  level, 
  track_id, 
  lang_id,
  start_date, 
  end_date,
  created_by_parent
);

-- Index for date_tasks_mission_marks: LEFT JOIN optimization
-- Query: LEFT JOIN date_tasks_mission_marks ON date_tasks_mission_id = ? AND user_id = ?
-- Used in: classes/user_track.php::update_user_track()
CREATE INDEX idx_dtmm_mission_user ON date_tasks_mission_marks (date_tasks_mission_id, user_id);

-- ============================================================================
-- PRIORITY 2: USER LOADING OPTIMIZATION
-- ============================================================================

-- Index for users: WHERE school_id=? AND class_id=? AND user_registered>0
-- Query: SELECT u.* FROM users WHERE u.school_id=? AND u.class_id=? AND u.user_registered>0
-- Used in: mission_report/classes/missions.php::createMissions()
CREATE INDEX idx_users_school_class_reg ON users (school_id, class_id, user_registered);

-- Index for classes: JOIN and ORDER BY class_grade, class_sub
-- Query: JOIN classes c ON u.class_id = c.class_id ORDER BY c.class_grade, c.class_sub
-- Used in: mission_report/classes/missions.php::createMissions()
CREATE INDEX idx_classes_order ON classes (class_id, class_grade, class_sub);

-- Index for users: ORDER BY last, first after JOIN
-- Query: ORDER BY c.class_grade, c.class_sub, u.last, u.first
-- Used in: mission_report/classes/missions.php::createMissions()
-- NOTE: Consolidated into idx_users_class_reg_order (line 280)

-- ============================================================================
-- PRIORITY 3: ADDITIONAL OPTIMIZATIONS
-- ============================================================================

-- Index for subjects: JOIN and ORDER BY subject_ord
-- Query: JOIN subjects s USING (subject_id) ORDER BY s.subject_ord
-- Used in: classes/user.php::get_user_tracks()
CREATE INDEX idx_subjects_ord ON subjects (subject_id, subject_ord);

-- Index for date_tasks_missions: Alternative for update_user_track WHERE clause
-- Query: WHERE school_type_id=? AND subject_id=? AND track_id=? AND level=? AND start_date<?
-- Used in: classes/user_track.php::update_user_track()
CREATE INDEX idx_dtm_update ON date_tasks_missions (
  school_type_id, 
  subject_id, 
  track_id, 
  level, 
  start_date
);

-- ============================================================================
-- PRIORITY 1: DUCH AND MISSION PRINTING SYSTEM INDEXES
-- ============================================================================

-- Index for date_tasks_marks: Streak marks and accomplished tasks lookup
-- Query: SELECT DISTINCT mark_date FROM date_tasks_marks WHERE user_id=? AND mark_date>=? AND mark_date<=?
-- Used in: mobile/streaks/classes/class.streaks.php::getMarks() and db.php (accomplished tasks)
CREATE INDEX idx_dtm_marks_user_date ON date_tasks_marks (user_id, mark_date);

-- Index for date_tasks_marks: Accomplished tasks with mark_inactive filter
-- Query: SELECT ... FROM date_tasks_marks WHERE mark_inactive=0 AND user_id=? AND mark_date>=? AND mark_date<=?
-- Used in: db.php (accomplished tasks timeline for duch printing)
CREATE INDEX idx_dtm_marks_user_date_inactive ON date_tasks_marks (user_id, mark_date, mark_inactive);

-- ============================================================================
-- PRIORITY 2: DUCH AND MISSION PRINTING SYSTEM INDEXES
-- ============================================================================

-- Index for streak_tasks: Streaks lookup by user
-- Query: SELECT st.* FROM streak_tasks st WHERE st.user_id=?
-- Used in: mobile/streaks/classes/class.streaks.php::setStreaks()
CREATE INDEX idx_streak_tasks_user ON streak_tasks (user_id);

-- Index for date_tasks: Streak filtering and JOIN
-- Query: JOIN date_tasks dt USING (streak_id) WHERE dt.streak_show=1
-- Used in: mobile/streaks/classes/class.streaks.php::setStreaks()
CREATE INDEX idx_date_tasks_streak ON date_tasks (streak_id, streak_show);

-- Index for date_tasks_missions: JOIN for streaks
-- Query: JOIN date_tasks_missions dtm USING (date_tasks_mission_id, school_type_id, track_id, level)
-- Used in: mobile/streaks/classes/class.streaks.php::setStreaks()
CREATE INDEX idx_dtm_streak_join ON date_tasks_missions (date_tasks_mission_id, school_type_id, track_id, level);

-- Index for date_tasks: JOIN for streak marks
-- Query: JOIN date_tasks dt USING (date_task_id) WHERE dt.streak_id=?
-- Used in: mobile/streaks/classes/class.streaks.php::getMarks()
CREATE INDEX idx_date_tasks_streak_id ON date_tasks (date_task_id, streak_id);

-- Index for medal_marks: Medal awards timeline
-- Query: SELECT ... FROM medal_marks WHERE user_id=? AND date_awarded>=? AND date_awarded<=?
-- Used in: db.php (medal awards timeline for duch printing)
-- NOTE: Covered by idx_medal_marks_user_subject_date (line 138) - queries without subject_id can use this index

-- Index for rank_marks: Rank promotions timeline
-- Query: SELECT ... FROM rank_marks WHERE user_id=? AND date_promoted>=? AND date_promoted<=?
-- Used in: db.php (rank promotions timeline for duch printing)
-- NOTE: Covered by idx_rank_marks_user_date_filters (line 158)

-- ============================================================================
-- PRIORITY 2: MEDALS AND RANKS SYSTEM INDEXES
-- ============================================================================

-- Index for medal_marks: Medals by subject and date range with filters
-- Query: SELECT * FROM medal_marks WHERE user_id=? AND subject_id=? AND date_awarded>=? AND date_awarded<=? [AND date_received IS NULL]
-- Used in: classes/user.php::get_medals(), get_medals_two()
CREATE INDEX idx_medal_marks_user_subject_date ON medal_marks (user_id, subject_id, date_awarded, date_received);

-- Index for medal_marks: Check if medal was awarded
-- Query: SELECT count(*) FROM medal_marks WHERE user_id=? AND subject_id=? AND medal_ord=?
-- Used in: classes/user_track.php::set_medal_awarded()
CREATE INDEX idx_medal_marks_user_subject_ord ON medal_marks (user_id, subject_id, medal_ord);

-- Index for medal_marks: User medals grouped by subject (MAX aggregation)
-- Query: SELECT MAX(medal_ord) FROM medal_marks WHERE user_id=? GROUP BY subject_id
-- Used in: missions.php (line 50), classes/user.php::getRankInfo()
CREATE INDEX idx_medal_marks_user_subject_max ON medal_marks (user_id, subject_id, medal_ord DESC);

-- Index for medal_marks: Count total medals for user
-- Query: SELECT count(*) FROM medal_marks WHERE user_id=?
-- Used in: classes/user.php::getRankInfo()
CREATE INDEX idx_medal_marks_user_count ON medal_marks (user_id);

-- Index for rank_marks: Ranks by date range with filters
-- Query: SELECT * FROM rank_marks WHERE user_id=? AND date_promoted>=? AND date_promoted<=? [AND date_card_received IS NULL]
-- Used in: classes/user.php::get_ranks(), get_ranks_two()
CREATE INDEX idx_rank_marks_user_date_filters ON rank_marks (user_id, date_promoted, date_card_received, date_book_received);

-- Index for rank_marks: Get rank marks by rank_ord
-- Query: SELECT date_promoted FROM rank_marks WHERE rank_ord=? AND user_id=?
-- Used in: classes/user.php::get_rank_marks()
CREATE INDEX idx_rank_marks_ord_user ON rank_marks (rank_ord, user_id);

-- Index for medals_subjects: JOIN for medals by subject
-- Query: JOIN medals_subjects USING (subject_id, medal_ord)
-- Used in: missions.php (line 50)
CREATE INDEX idx_medals_subjects_composite ON medals_subjects (subject_id, medal_ord);

-- ============================================================================
-- PRIORITY 2: MISSIONS SYSTEM INDEXES
-- ============================================================================

-- Index for user_mission_entries: EXISTS subquery for missions page
-- Query: EXISTS (SELECT * FROM user_mission_entries WHERE user_id=? AND entry_type='date_tasks_missions' AND entry_id=?)
-- Used in: missions.php (line 43)
CREATE INDEX idx_user_mission_entries_lookup ON user_mission_entries (user_id, entry_type, entry_id);

-- Index for date_tasks: JOIN for EXISTS subquery
-- Query: JOIN date_tasks WHERE date_tasks.date_tasks_mission_id=?
-- Used in: missions.php (line 41, 45)
CREATE INDEX idx_date_tasks_mission_id ON date_tasks (date_task_id, date_tasks_mission_id);

-- Index for date_tasks_marks: JOIN for EXISTS subquery
-- Query: JOIN date_tasks_marks USING (date_task_id) WHERE user_id=?
-- Used in: missions.php (line 41, 45)
CREATE INDEX idx_date_tasks_marks_task_user ON date_tasks_marks (date_task_id, user_id);

-- Index for medal_marks: Shipping queries by date and school
-- Query: SELECT ... FROM medal_marks WHERE date_awarded>=? AND date_awarded<=? [AND school_id=?] ORDER BY user_id, medal_ord
-- Used in: reports/shipping/functions/get_medals.php
CREATE INDEX idx_medal_marks_shipping ON medal_marks (date_awarded, user_id, school_id, medal_ord);

-- Index for rank_marks: Shipping queries by date and school
-- Query: SELECT ... FROM rank_marks WHERE date_promoted>=? AND date_promoted<=? [AND school_id=?]
-- Used in: reports/shipping/functions/get_ranks.php
CREATE INDEX idx_rank_marks_shipping ON rank_marks (date_promoted, user_id, school_id);

-- ============================================================================
-- PRIORITY 3: ADDITIONAL DUCH AND MISSION PRINTING INDEXES
-- ============================================================================

-- Index for parshos: Parsha lookup by date range
-- Query: SELECT name FROM parshos WHERE start=? AND end=?
-- Used in: mission_report/classes/missionDisplay.php::__construct()
CREATE INDEX idx_parshos_dates ON parshos (start, end);

-- Index for sponsorships: Sponsorships lookup by date range
-- Query: SELECT * FROM sponsorships WHERE start_date>=? AND end_date<=? LIMIT 1
-- Used in: mission_report/classes/missionDisplay.php (line 377)
CREATE INDEX idx_sponsorships_dates ON sponsorships (start_date, end_date);

-- ============================================================================
-- PRIORITY 4: SHABBOS MEVORCHIM TEHILLIM SYSTEM INDEXES
-- ============================================================================

-- Index for date_tasks: Grid ID filtering for tehillim queries
-- Query: JOIN date_tasks dt WHERE dt.grid_id=? (grid_id 8001 for Kapitelach, 8002 for Minutes)
-- Used in: class.shabbosMevorchim.php::setArmyResults(), setClassResults(), setWhatsappStudentResults()
CREATE INDEX idx_date_tasks_grid_mission ON date_tasks (grid_id, date_tasks_mission_id);

-- Index for date_tasks_missions: Mission dates and school type for tehillim
-- Query: JOIN date_tasks_missions WHERE start_date=? AND end_date=? AND school_type_id=? AND lang_id=?
-- Used in: class.shabbosMevorchim.php (multiple methods)
CREATE INDEX idx_dtm_mission_dates_school ON date_tasks_missions (date_tasks_mission_id, start_date, end_date, school_type_id, lang_id);

-- Index for user_tracks: Composite index for tehillim JOINs
-- Query: JOIN user_tracks ut USING (track_id, level, subject_id) WHERE ut.subject_id=1 AND ut.enrolled=1
-- Used in: class.shabbosMevorchim.php (army, school, class, student queries)
CREATE INDEX idx_user_tracks_composite ON user_tracks (user_id, track_id, level, subject_id, enrolled);

-- Index for users: Composite index for tehillim user filtering
-- Query: JOIN users u WHERE u.user_registered>0 AND u.school_type_id=? AND u.lang_id=?
-- Used in: class.shabbosMevorchim.php (army and school queries)
CREATE INDEX idx_users_school_composite ON users (user_id, school_id, user_registered, school_type_id, lang_id);

-- Index for classes: Class era filtering
-- Query: JOIN classes c WHERE c.class_era=0
-- Used in: class.shabbosMevorchim.php (army and school queries)
CREATE INDEX idx_classes_era ON classes (class_id, class_era);

-- Index for schools: School era filtering
-- Query: JOIN schools s WHERE s.school_era IS NULL
-- Used in: class.shabbosMevorchim.php::setArmyResults()
CREATE INDEX idx_schools_era ON schools (school_id, school_era);

-- Index for date_tasks: Task ID and grid ID for done marks queries
-- Query: JOIN date_tasks dt USING (date_task_id) WHERE dt.grid_id=?
-- Used in: class.shabbosMevorchim.php (done marks queries)
CREATE INDEX idx_date_tasks_task_grid ON date_tasks (date_task_id, grid_id);

-- Index for date_tasks_missions: Mission dates for done marks
-- Query: JOIN date_tasks_missions WHERE start_date=? AND end_date=?
-- Used in: class.shabbosMevorchim.php (done marks queries)
CREATE INDEX idx_dtm_marks_mission_dates ON date_tasks_missions (date_tasks_mission_id, start_date, end_date);

-- Index for date_tasks_marks: User and task for done marks
-- Query: FROM date_tasks_marks WHERE user_id=? AND date_task_id=?
-- Used in: class.shabbosMevorchim.php::setWhatsappStudentResults()
CREATE INDEX idx_dtm_marks_user_task ON date_tasks_marks (user_id, date_task_id);

-- Index for tehillim_backups: Date and grid ID for backup lookups
-- Query: SELECT ... FROM tehillim_backups WHERE sm_date=? AND grid_id=?
-- Used in: class.shabbosMevorchim.php (multiple methods for backup table queries)
CREATE INDEX idx_tehillim_backups_dates_grid ON tehillim_backups (sm_date, grid_id);

-- Index for tehillim_backups: Per-user backup lookups
-- Query: SELECT ... FROM tehillim_backups WHERE sm_date=? AND grid_id=? AND user_id=?
-- Used in: class.shabbosMevorchim.php::setWhatsappStudentResults()
CREATE INDEX idx_tehillim_backups_user ON tehillim_backups (sm_date, grid_id, user_id);

-- Index for date_tasks_missions: Complete mission lookup for student quotas
-- Query: JOIN date_tasks_missions WHERE subject_id=1 AND start_date=? AND end_date=? AND school_type_id=? AND lang_id=?
-- Used in: class.shabbosMevorchim.php::setWhatsappStudentResults()
CREATE INDEX idx_dtm_mission_complete ON date_tasks_missions (date_tasks_mission_id, subject_id, start_date, end_date, school_type_id, lang_id);

-- Index for users: Class filtering with ORDER BY for tehillim users
-- Query: SELECT u.* FROM users u WHERE class_id=? AND user_registered>0 ORDER BY last, first
-- Used in: class.shabbosMevorchim.php::setWhatsappStudentResults(), setStudentResults()
CREATE INDEX idx_users_class_reg_order ON users (class_id, user_registered, last, first);

-- Index for user_tracks: User and subject filtering for tehillim
-- Query: JOIN user_tracks ut USING (user_id) WHERE ut.subject_id=1
-- Used in: class.shabbosMevorchim.php::setStudentResults()
-- NOTE: Covered by idx_user_tracks_composite (line 230)

-- ============================================================================
-- PRIORITY 5: CHIDON TESTING SYSTEM INDEXES
-- ============================================================================

-- Index for th_chidon: Year and user lookup
-- Query: SELECT ... FROM th_chidon WHERE year=? AND user_id=?
-- Used in: chidonTests/class.chidonTests.php::setStudents()
CREATE INDEX idx_th_chidon_year_user ON th_chidon (year, user_id);

-- Index for th_chidon_info: Year and user lookup for highest track
-- Query: SELECT highest_track FROM th_chidon_info WHERE year=? AND user_id=?
-- Used in: chidonTests/class.chidonTests.php::getHighestTrack()
CREATE INDEX idx_th_chidon_info_year_user ON th_chidon_info (year, user_id);

-- Index for users: Composite index for chidon JOINs
-- Query: JOIN users u WHERE u.school_id=? AND u.class_id=? AND u.gender=?
-- Used in: chidonTests/class.chidonTests.php::setStudents()
CREATE INDEX idx_users_chidon_composite ON users (user_id, school_id, class_id, gender);

-- Index for th_chidon_marks: Scores lookup by chidon ID
-- Query: SELECT * FROM th_chidon_marks WHERE th_chidon_id=?
-- Used in: chidonTests/class.chidonTests.php::setScores()
CREATE INDEX idx_th_chidon_marks_id ON th_chidon_marks (th_chidon_id);

-- Index for chidon_passing_avgs: Passing averages lookup
-- Query: SELECT * FROM chidon_passing_avgs WHERE year=? AND (user_id=? OR school_id=? OR class_id=?)
-- Used in: chidonTests/class.chidonTests.php::getPassingAvgs()
CREATE INDEX idx_chidon_passing_avgs ON chidon_passing_avgs (year, user_id, school_id, class_id);

-- Index for chidon_test_levels: Test levels lookup
-- Query: SELECT * FROM chidon_test_levels WHERE year=? AND test_type=? AND (user_id=? OR school_id=? OR class_id=?)
-- Used in: chidonTests/class.chidonTests.php::getLevel()
CREATE INDEX idx_chidon_test_levels ON chidon_test_levels (year, test_type, user_id, school_id, class_id);

-- Index for th_chidon: User and year for eligibility history
-- Query: SELECT * FROM th_chidon WHERE user_id=? AND year>=?
-- Used in: chidonTests/class.chidonTests.php::enrollmentEligibility(), getEligibilityFromHistory()
CREATE INDEX idx_th_chidon_user_year ON th_chidon (user_id, year);

-- ============================================================================
-- PRIORITY 6: CHIDON SHIPPING SYSTEM INDEXES
-- ============================================================================

-- Index for th_chidon: Year, user, and khk_reg for brochures and guides
-- Query: SELECT * FROM th_chidon WHERE year=? [AND khk_reg=1]
-- Used in: chidon_shipping/class.chidonShipping.php::getBrochures(), getGuides()
CREATE INDEX idx_th_chidon_year_khk ON th_chidon (year, user_id, khk_reg);

-- Index for th_chidon: Year and date_paid for gifts and ID cards
-- Query: SELECT * FROM th_chidon WHERE date_paid>0 AND year=?
-- Used in: chidon_shipping/class.chidonShipping.php::getGifts(), getIDCards()
CREATE INDEX idx_th_chidon_year_paid ON th_chidon (year, date_paid, user_id);

-- Index for th_chidon_finals: Finals lookup for awards
-- Query: SELECT * FROM th_chidon_finals WHERE year=? JOIN th_chidon USING (user_id, year)
-- Used in: chidon_shipping/class.chidonShipping.php::getAwardsByFinals()
CREATE INDEX idx_th_chidon_finals_year_user ON th_chidon_finals (year, user_id);

-- Index for th_chidon: User and year for JOINs
-- Query: JOIN th_chidon tc USING (user_id, year)
-- Used in: chidon_shipping/class.chidonShipping.php (multiple methods)
-- NOTE: Already defined earlier (line 324) - this is a duplicate reference

-- Index for chidon_user_prizes: User lookup for shipping report
-- Query: JOIN chidon_user_prizes cup USING (user_id)
-- Used in: chidon_shipping/report.php
CREATE INDEX idx_chidon_user_prizes_user ON chidon_user_prizes (user_id);

-- Index for users: School, gender, and ORDER BY for shipping report
-- Query: WHERE u.school_id IN (?) [AND u.gender=?] ORDER BY u.school_id, u.last, u.first
-- Used in: chidon_shipping/report.php
CREATE INDEX idx_users_school_gender_order ON users (school_id, gender, last, first);

-- Index for registration_charges: Chidon charges lookup
-- Query: SELECT ... FROM registration_charges WHERE (type LIKE '%LDE%' OR type LIKE '%YB%') AND year=?
-- Used in: reports/chidon/combined.php, chidon_shipping/class.chidonShipping.php::getYahadusBooks()
CREATE INDEX idx_reg_charges_chidon ON registration_charges (year, user_id, type, date, school_id);

-- Index for registration_charges: Shipping status filtering
-- Query: WHERE (type LIKE '%LDE%' AND study_guide_shipped=0) OR (type LIKE '%YB%' AND book_shipped=0)
-- Used in: reports/chidon/combined.php
CREATE INDEX idx_reg_charges_shipping ON registration_charges (year, type, study_guide_shipped, book_shipped);

-- Index for th_chidon: Recruitment credits lookup
-- Query: JOIN th_chidon tc ON u.user_serial = tc.recruited_by WHERE year=?
-- Used in: chidon_shipping/class.chidonShipping.php::getChildrenRecruitments()
CREATE INDEX idx_th_chidon_recruited ON th_chidon (year, recruited_by);

-- Index for users: User serial composite for recruitment JOINs
-- Query: JOIN users u ON u.user_serial = tc.recruited_by WHERE u.gender=? AND u.school_id=?
-- Used in: chidon_shipping/class.chidonShipping.php::getChildrenRecruitments()
CREATE INDEX idx_users_serial_composite ON users (user_serial, user_id, gender, school_id);

-- ============================================================================
-- PRIORITY 7: AUCTION SYSTEM INDEXES
-- ============================================================================

-- Index for auction_winners: Auction winners lookup
-- Query: SELECT ... FROM auction_winners WHERE auction_id=? AND user_id=? AND prize_id=?
-- Used in: reports/shipping/functions/get_auctions.php, admin_auction_run.php
CREATE INDEX idx_auction_winners_composite ON auction_winners (auction_id, user_id, prize_id);

-- Index for auctions: Auction filtering by ran status and date
-- Query: SELECT * FROM auctions WHERE auction_ran=0 [AND school_id=?] ORDER BY auction_date DESC
-- Used in: admin_auction_run.php, db.php::auctionCurrent()
CREATE INDEX idx_auctions_ran_school_date ON auctions (auction_ran, school_id, auction_date DESC);

-- Index for auctions: Auction filtering by run date
-- Query: SELECT * FROM auctions WHERE auction_ran=0 AND (auction_run_date IS NULL OR auction_run_date > ?)
-- Used in: db.php::auctionCurrent()
CREATE INDEX idx_auctions_ran_run_date ON auctions (auction_ran, auction_run_date);

-- Index for auction_prizes: Prize availability lookup
-- Query: SELECT ... FROM auction_prizes WHERE auction_id=? AND available=?
-- Used in: auction/class.auction.php::setGrandPrizes(), setPrizes()
CREATE INDEX idx_auction_prizes_auction_available ON auction_prizes (auction_id, prize_id, available);

-- Index for prizes_auction: Prize points filtering
-- Query: SELECT ... FROM prizes_auction WHERE prize_points>=72 [OR prize_points<72]
-- Used in: auction/class.auction.php::setGrandPrizes(), setPrizes()
CREATE INDEX idx_prizes_auction_points ON prizes_auction (prize_id, prize_points);

-- ============================================================================
-- PRIORITY 7: RAFFLE SYSTEM INDEXES
-- ============================================================================

-- Index for raffle_winners: Raffle winners lookup
-- Query: SELECT ... FROM raffle_winners WHERE raffle_id=? AND user_id=? AND prize_id=?
-- Used in: raffles/shared/classes/Raffle.php::get_winner_info(), mobile/api/raffles/functions.php
CREATE INDEX idx_raffle_winners_composite ON raffle_winners (raffle_id, user_id, prize_id);

-- Index for raffles: Raffle filtering by run date
-- Query: SELECT * FROM raffles WHERE DATE(run_date)=CURDATE() AND date_ran IS NULL
-- Used in: raffles/tasks/run_raffle/run_raffle.php
CREATE INDEX idx_raffles_run_date_ran ON raffles (run_date, date_ran);

-- Index for raffles: Raffle filtering by type and year
-- Query: SELECT * FROM raffles WHERE type=? AND year=?
-- Used in: mobile/api/raffles/functions.php::getWinnersInfo()
CREATE INDEX idx_raffles_type_year ON raffles (type, year);

-- Index for raffles: Raffle ID and type for JOINs
-- Query: JOIN raffles USING (raffle_id) WHERE raffles.type=?
-- Used in: raffles/shared/shipping/functions/getWinners.php
CREATE INDEX idx_raffles_id_type ON raffles (raffle_id, type);

-- Index for admin_auths: Admin auth lookup
-- Query: LEFT JOIN admin_auths ON user_id = id
-- Used in: raffles/shared/classes/Raffle.php::get_winner_info()
-- NOTE: Covered by idx_admin_auths_id_admin_role (line 628)

-- ============================================================================
-- PRIORITY 7: ACHIEVEMENT CARD SYSTEM INDEXES
-- ============================================================================

-- Index for achievement_cards: Card serial lookup
-- Query: SELECT * FROM achievement_cards WHERE card_serial=?
-- Used in: v2/application/models/AchievementCards.php::achievement_card_validation(), deposit_points()
CREATE INDEX idx_achievement_cards_serial ON achievement_cards (card_serial);

-- Index for achievement_cards: ORDER BY modified and created
-- Query: SELECT * FROM achievement_cards ORDER BY modified DESC, created DESC
-- Used in: v2/application/models/AchievementCards.php::_achievement_cards_select()
CREATE INDEX idx_achievement_cards_modified_created ON achievement_cards (modified DESC, created DESC);

-- Index for achievement_cards: Filtering by institution, campaign, and type
-- Query: SELECT ... FROM achievement_cards WHERE institution_id=? AND created_by=? AND card_type=?
-- Used in: v2/application/models/Campaigns.php::select_achievement_card_templates()
CREATE INDEX idx_achievement_cards_institution_campaign_type ON achievement_cards (institution_id, created_by, card_type, campaign_id);

-- ============================================================================
-- PRIORITY 8: SHIPPING REPORTS SYSTEM INDEXES
-- ============================================================================

-- Index for shipments: Shipment filtering by school, date, and status
-- Query: SELECT * FROM shipments WHERE school_id=? [AND date_shipped>?] [AND status IN (?)]
-- Used in: reports/shipping/classes/Shipment.php::loadAll(), reports/shipping/shipments/ajax/report.php
CREATE INDEX idx_shipments_school_date_status ON shipments (school_id, date_shipped, status);

-- Index for shipment_details: Shipment details lookup
-- Query: SELECT COUNT(*) FROM shipment_details WHERE shipment_id=?
-- Used in: reports/shipping/classes/Shipment.php::get_detail_count()
CREATE INDEX idx_shipment_details_shipment ON shipment_details (shipment_id);

-- Index for shipment_details: Shipment details with item filtering
-- Query: SELECT ... FROM shipment_details WHERE shipment_id=? AND type=? AND item_id=?
-- Used in: reports/shipping/classes/Shipment.php::get_details()
CREATE INDEX idx_shipment_details_composite ON shipment_details (shipment_id, type, item_id, item_ord, item_extra_id);

-- Index for tracking_numbers: Tracking numbers by shipment
-- Query: SELECT * FROM tracking_numbers WHERE shipment_id=?
-- Used in: reports/shipping/classes/Shipment.php::get_tracking_numbers()
CREATE INDEX idx_tracking_numbers_shipment ON tracking_numbers (shipment_id);

-- Index for tracking_numbers: Tracking numbers by school
-- Query: SELECT * FROM tracking_numbers WHERE school_id=?
-- Used in: reports/shipping/functions/get_tracking_numbers.php
CREATE INDEX idx_tracking_numbers_school ON tracking_numbers (school_id);

-- ============================================================================
-- PRIORITY 8: TANYA SYSTEM INDEXES
-- ============================================================================

-- Index for lines_learned: Lines learned by campaign and user
-- Query: SELECT lines_learned FROM lines_learned WHERE campaign_id=? AND user_id=?
-- Used in: class.tanya.php::getUsersTotalLearned(), class.balPehCampaign.php::getTotalLearned()
CREATE INDEX idx_lines_learned_campaign_user ON lines_learned (campaign_id, user_id);

-- Index for lines_learned: Total lines learned aggregation
-- Query: SELECT sum(lines_learned) FROM lines_learned WHERE campaign_id=? AND user_id IN (?)
-- Used in: class.balPehCampaign.php::getTotalLearned() (school/class totals)
CREATE INDEX idx_lines_learned_campaign_user_sum ON lines_learned (campaign_id, user_id, lines_learned);

-- Index for tanya_users: Tanya user info lookup
-- Query: SELECT ... FROM tanya_users WHERE user_id=?
-- Used in: db.php (line 685), admin_stats.php (line 464)
CREATE INDEX idx_tanya_users_user ON tanya_users (user_id);

-- Index for line_campaigns: Latest campaign by type
-- Query: SELECT id FROM line_campaigns WHERE type=? ORDER BY id DESC LIMIT 1
-- Used in: class.tanya.php::__construct()
CREATE INDEX idx_line_campaigns_type_id ON line_campaigns (type, id DESC);

-- ============================================================================
-- PRIORITY 8: MIVTZOIM SYSTEM INDEXES
-- ============================================================================

-- Index for date_tasks: Tasks by short_name for mivtzoim
-- Query: SELECT ... FROM date_tasks WHERE short_name=? AND date_tasks_mission_id=?
-- Used in: mivtzoim/classes/mivtzoim.php::getTasks()
CREATE INDEX idx_date_tasks_short_name ON date_tasks (short_name, date_tasks_mission_id);

-- Index for date_tasks_marks: Mivtzoim marks aggregation
-- Query: SELECT SUM(done_qty) FROM date_tasks_marks WHERE date_task_id IN (?) AND mark_date>=? AND mark_date<=? GROUP BY user_id
-- Used in: mivtzoim/classes/mivtzoim.php::calculateUserMarks()
CREATE INDEX idx_date_tasks_marks_task_user_date_qty ON date_tasks_marks (date_task_id, user_id, mark_date, done_qty);

-- ============================================================================
-- PRIORITY 8: 12 PESUKIM SYSTEM INDEXES
-- ============================================================================

-- Index for pesukim_recruits: Recruits by recruiter
-- Query: SELECT * FROM pesukim_recruits WHERE recruiter_id=?
-- Used in: pesukim/class.pesukim.php::getRecruits()
CREATE INDEX idx_pesukim_recruits_recruiter ON pesukim_recruits (recruiter_id, recruited_id);

-- Index for pesukim_recruits: Recruiter lookup by recruited
-- Query: SELECT recruiter_id FROM pesukim_recruits WHERE recruited_id=?
-- Used in: pesukim/class.pesukim.php::getRecruiter()
CREATE INDEX idx_pesukim_recruits_recruited ON pesukim_recruits (recruited_id);

-- Index for pesukim_duch_recruits: Duch recruits by user
-- Query: SELECT * FROM pesukim_duch_recruits WHERE user_id=?
-- Used in: pesukim/class.pesukim.php::getDuchRecruits()
CREATE INDEX idx_pesukim_duch_recruits_user ON pesukim_duch_recruits (user_id);

-- Index for pesukim_mechunachim: Mechunachim by user
-- Query: SELECT * FROM pesukim_mechunachim WHERE mechanech_user_id=?
-- Used in: pesukim/class.pesukim.php::getMechunachim()
CREATE INDEX idx_pesukim_mechunachim_user ON pesukim_mechunachim (mechanech_user_id);

-- Index for pesukim_mechunachim: Verification lookup
-- Query: UPDATE pesukim_mechunachim WHERE mechanech_user_id=? AND mechunach_id=? AND verification_code=?
-- Used in: pesukim/class.pesukim.php::verifyMechunach()
CREATE INDEX idx_pesukim_mechunachim_verify ON pesukim_mechunachim (mechanech_user_id, mechunach_id, verification_code);

-- ============================================================================
-- Indexes for Chidon Drive system
-- ============================================================================

-- For Query 80: Chidon drive children lookup
CREATE INDEX idx_chidon_confirmations_year_school ON chidon_confirmations (year, school_id);
CREATE INDEX idx_chidon_open_reg_year_school ON chidon_open_reg (year, school_id);
CREATE INDEX idx_admin_auths_id_admin_role ON admin_auths (id, admin_id, role_id);

-- For Query 81: Chidon drive subsidies lookup
CREATE INDEX idx_chidon_user_subsidies_year_user ON chidon_user_subsidies (chidon_year, user_id, subsidy_amount);

-- For Query 83: Keep registration open
CREATE INDEX idx_keep_reg_open_year ON keep_reg_open (year);

-- For Query 84: Chidon drive donations
CREATE INDEX idx_chidon_donations_year_id ON chidon_donations (chidon_year, chidon_donation_id);
CREATE INDEX idx_chidon_user_subsidies_donation_year ON chidon_user_subsidies (chidon_donation_id, chidon_year, user_id);

-- For Query 85: Chidon drive totals
CREATE INDEX idx_chidon_donations_year_amount ON chidon_donations (chidon_year, donation_amount);
CREATE INDEX idx_th_chidon_year_rohr_paid ON th_chidon (year, rohr_subsidy, paid);
-- NOTE: idx_th_chidon_year_paid defined earlier (line 338) with user_id column

-- For Query 86: Chidon drive family donations
CREATE INDEX idx_chidon_donations_year_family ON chidon_donations (chidon_year, for_family_id, donation_amount);

-- For Query 88: Chidon drive child subsidies
CREATE INDEX idx_th_chidon_user_year_subsidy ON th_chidon (user_id, year, rohr_subsidy, paid);

-- For Query 89: Chidon drive family children
CREATE INDEX idx_th_chidon_user_year_fundraising ON th_chidon (user_id, year, fundraising_goal);

-- For Query 90 & 91: Chidon drive school/community children
CREATE INDEX idx_th_chidon_year_school_fundraising ON th_chidon (year, school_id, fundraising_goal, user_id);

-- For Query 94: Chidon confirmations
-- NOTE: Covered by idx_chidon_confirmations_year_school (line 626)

-- For Query 95: Chidon open registration
-- NOTE: Covered by idx_chidon_open_reg_year_school (line 627)

-- ============================================================================
-- Indexes for Promotions system
-- ============================================================================

-- For Query 96: Promotions by date range
CREATE INDEX idx_rank_marks_date_ord_user ON rank_marks (date_promoted, rank_ord DESC, user_id);
CREATE INDEX idx_users_reg_gender_school_order ON users (user_id, user_registered, gender, school_id, last, first);

-- For Query 97: Today's promotions
CREATE INDEX idx_rank_marks_date_user_ord ON rank_marks (date_promoted, user_id, rank_ord DESC);

-- ============================================================================
-- Indexes for Store and Prizes system
-- ============================================================================

-- For Query 98: Store prizes lookup
-- NOTE: Covered by idx_prizes_institution_active_count_points_name (line 635)

-- For Query 99 & 117: Prize classes lookup
CREATE INDEX idx_prize_classes_prize_class ON prize_classes (prize_id, class_id);

-- For Query 100: Store prizes for user
-- NOTE: Covered by idx_prizes_institution_active_count_points_name (line 635)

-- For Query 101: Check one-time prize limit
CREATE INDEX idx_user_prizes_prize_user_reversed_created ON user_prizes (prize_id, user_id, is_reversed, created);

-- For Query 102: Get user orders
CREATE INDEX idx_user_prizes_user_created ON user_prizes (user_id, created DESC);
CREATE INDEX idx_deleted_prizes_prize ON deleted_prizes (prize_id);

-- For Query 103: Get orders list
CREATE INDEX idx_user_prizes_reversed_institution_status_created ON user_prizes (is_reversed, institution_id, status, created DESC);
CREATE INDEX idx_user_prizes_reversed_class_status_created ON user_prizes (is_reversed, class_id, status, created DESC);
CREATE INDEX idx_users_user_class_order ON users (user_id, class_id, first, last);
CREATE INDEX idx_user_points_user_prize ON user_points (user_prize_id);

-- For Query 104: Get store for single user
CREATE INDEX idx_prizes_institution_active_count_points_name ON prizes (institution_id, is_active, prize_count, prize_id, points, prize_name);
CREATE INDEX idx_user_prizes_prize_user_reversed_id ON user_prizes (prize_id, user_id, is_reversed, user_prize_id);

-- For Query 106 & 120: Check serial uniqueness
CREATE INDEX idx_user_prizes_serial ON user_prizes (serial);

-- For Query 107: Place order - user prizes
CREATE INDEX idx_user_prizes_prize_user_institution ON user_prizes (prize_id, user_id, institution_id);

-- For Query 108: Place order - user points
CREATE INDEX idx_user_points_user_prize_user_institution ON user_points (user_prize_id, user_id, institution_id);

-- For Query 111: Reverse orders - user points
CREATE INDEX idx_user_prizes_prize_user_qty_institution ON user_prizes (user_prize_id, prize_id, user_id, quantity, institution_id);

-- For Query 113: Get store purchases (legacy)
CREATE INDEX idx_store_purchases_user_points_qty ON store_purchases (user_id, prize_points, prize_quantity);

-- For Query 114: Get cart items (legacy)
CREATE INDEX idx_store_purchases_user_prize_shipped ON store_purchases (user_id, prize_id, prize_shipped);

-- For Query 118: Check payment processing
CREATE INDEX idx_payment_processing_user ON payment_processing (user_id);

-- For Query 119: Get user store points
CREATE INDEX idx_user_points_user_resource_points ON user_points (user_id, resource_name, points);
