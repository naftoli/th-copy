<?
require_once __DIR__ . '/class.birthday.php';

class BirthdayEn extends Birthday {
	protected $lang_id = 1;
	protected $description = 'Yom Holedes Mission';
	protected $category = 'Birthday';
	protected $mandTasks = array(
		'I said my new kapital.', 
		'I made a farbrengen to thank Hashem for reaching this special day.', 
		'I made a new hachlata. What is your hachlata? ________________________'
	);
	protected $optTasks = array(
		'I davened with extra kavana.',  
		'I gave extra tzedoka before Shacharis (and Mincha. If my birthday was on Shabbos, I gave extra before and after.)', 
		'I learnt my new kapital.',
		'I made a Cheshbon Hanefesh to go over which areas of my life I am doing well in and in which areas I need to improve.', 
		'I made a Shehechiyanu on a new fruit.', 
		'I learnt extra Torah. What did you learn? ________________________', 
		'I had extra Ahavas Yisroel. How did you have extra Ahavas Yisroel? ________________________' 
	);
	protected $qtyTasks = array(
		'1-150' =>  'I said extra tehillim. How many extra kapitelach did you say?'
	);
	protected $ageTasks = array(
		'10' => 'I learned part of a Maamer by heart and reviewed it (on my birthday or on the Shabbos after). Which Maamer did you learn? __________________',
		'13' => 'On the Shabbos before my birthday (and if my birthday fell out on a day the Torah is read, on that day) I was called up for an Aliya.'
	);

	protected function missionName($yomHoledes, $yearsOld) {
		switch ($yearsOld) {
			case 1:
				$ext = "st Birthday!";
				break;
			case 2:
				$ext = "nd Birthday!";
				break;
			case 3:
				$ext = "rd Birthday!";
				break;
			default:
				$ext = "th Birthday!";
				break;
		}
		return $yomHoledes . " - Happy " . $yearsOld . $ext;
	}
}
