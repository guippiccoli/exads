<?php

class Exads {

	private function printShellTitle($title) {
		echo str_repeat('-', 40), PHP_EOL, $title, PHP_EOL, str_repeat('-', 40), PHP_EOL;
	}

	// 1. FizzBuzz
	public function questionOne() {
		$this->printShellTitle('1. FizzBuzz');

		for($i = 1; $i <= 100; $i++) {
			// If not multiple of 3 or 5, just print the number of iteration
			if($i % 3 !== 0 && $i % 5 !== 0) {
				echo $i;
			} else {
				// If is multiple of 3 or 5, checks for which one and print the respective string 
				if($i % 3 === 0) {
					echo 'Fizz ';
				}
				if($i % 5 === 0) {
					echo 'Buzz';
				}
			}
			echo PHP_EOL;
		}
	}

	// 2. 500 Element Array
	public function questionTwo() {
		$this->printShellTitle('2. 500 Element Array');

		// Initiate the array using the range function
		// Save the sum of the array values
		// Shuffles its elements
		$range = 500;
		$array = range(1, $range);
		$originalSumOfArray = array_sum($array);
		shuffle($array);

		// Choose a random integer to unset its index from the array
		// Save the sum of the array values after unseting a random index
		$elementToRemove = random_int(1, $range);
		unset($array[$elementToRemove]);
		$finalSumOfArray = array_sum($array);

		// Calculate the missing number by subtracting the original sum of array by the sum after the unset
		$unsetValue = $originalSumOfArray - $finalSumOfArray;
		echo 'Value removed from array: ', $unsetValue;
	}

	// 3. Database Connectivity
	public function questionThree() {
		$this->printShellTitle('3. Database Connectivity');

		try {
			// Connecting to DB
			$pdo = new PDO('mysql:host=localhost;dbname=exads', 'root', 'root');

			// Getting all records
			$query = $pdo->prepare("SELECT `name`, `age`, `job_title` FROM exads_test");
			$query->execute();
			$allRecords = $query->fetchAll();
			print_r($allRecords);

			// Inserting sanitezed data
			$name = 'Guilherme Piccoli';
			$age = 27;
			$job_title = 'Ninja';
			$insert = $pdo->prepare("INSERT INTO exads_test (`name`, `age`, `job_title`) VALUES (:name, :age, :job_title)");
			$insert->bindParam(':name', $name, PDO::PARAM_STR);
            $insert->bindParam(':age', $age, PDO::PARAM_INT);
            $insert->bindParam(':job_title', $job_title, PDO::PARAM_STR);
			$insert->execute();
		}
		catch (PDOException $e) {
			print_r($e);
		}
	}

	// 4. Date Calculation
	// This one is tricky. The easy way would be using PHP DateTime and play with its modifiers/matchers.
	// The easy way with DateTime() would be something using $date->modify('next saturday 20:00')
	// I assumed that do that would be kind of cheating on a code test, so I did not do that.
	public function questionFour() {
		$this->printShellTitle('4. Date Calculation');

		$customDate = '2019-02-06 20:05:00'; // Set null to current date
		$dateToday = date($customDate ? $customDate : 'Y-m-d H:i:s'); // Brings the current date to dateFormat
		$dateInfo = $this->getDateInfo($dateToday); // Get information about the date (weekdays, hours, etc)
		$weekday = $dateInfo['weekday'];
		$hours = $dateInfo['hours'];
		$nextDraw = $this->getNextDrawDate($dateToday); // Get next valid draw date

		echo 'Today: ', $dateToday, ' - ',  $this->getDateInfo($dateToday)['weekday'];
		echo PHP_EOL;
		echo 'Next draw: ', $nextDraw, ' - ',  $this->getDateInfo($nextDraw)['weekday'];

	}

	// Get next valid draw day.
	private function getNextDrawDate($dateDraw) {
		$dateInfo = $this->getDateInfo($dateDraw);
		if(($dateInfo['weekday'] === 'Saturday' || $dateInfo['weekday'] === 'Wednesday') && $dateInfo['hours'] < 20) {
			return date('Y-m-d 20:00:00', strtotime($dateDraw));
		}
		do {
			$dateDraw = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($dateDraw)));
			$weekday = $this->getDateInfo($dateDraw)['weekday'];
		} while($weekday !== 'Wednesday' && $weekday !== 'Saturday');

		return date('Y-m-d 20:00:00', strtotime($dateDraw));
	}

	// Get informations about date (weekday, hours, etc)
	private function getDateInfo($date) {
		return getdate(strtotime($date));
	}

	// 5. A/B Testing
	public function questionFive() {
		$this->printShellTitle('A/B Testing');
		
		$data = $this->getQuestionFiveData();
		$desingToShow = $this->getDesingToShow($data);

		echo 'Design to redirect: ', PHP_EOL;
		print_r($desingToShow);
		return $desingToShow;
	}

	// Return the Desing to show user, based on the chances specified in the dataset.
	// The consistency can be tested by $this->questionFiveStressTest()
	private function getDesingToShow($data) {
		if($this->isQuestionFiveDataInvalid($data)) {
			throw new Exception('Question five dataset Error: All fields "split_percent" of the array must sum to exact 100.');
			return false;
		}
		$totalChances = 0;
		$userChance = rand(1, 100);
		foreach($data as $design) {
			$totalChances += $design['split_percent'];
			if($totalChances >= $userChance) {
				$desingToShow = $design;
				break;
			}
		}
		return $desingToShow;
	}

	// Just a little test to check the behavior of the A/B Testing algorithm
	// You chose how many times it will run modifing the variable $runUsers
	// It will insert a field "hits" inside the data structure and increment by the percentage given
	// On the end, "hits" should the "split_percent" of $runUsers
	public function questionFiveStressTest() {
		$this->printShellTitle('A/B Testing Stress Test');
		$runUsers = 10000;
		$data = $this->getQuestionFiveData();
		$result = [];
		foreach($data as $d) {
			$result[$d['design_id']] = $d;
			$result[$d['design_id']]['hits'] = 0;
		}

		for($i = 0; $i < $runUsers; $i++) {
			$desingToShow = $this->getDesingToShow($data);
			$result[$desingToShow['design_id']]['hits']++;
		}

		print_r($result);
	}

	// Defining the dataset for question five
	private function getQuestionFiveData ()  {
		return [
			0 => ['design_id' => 1, 'design_name' => 'Design 1', 'split_percent' => 50],
			1 => ['design_id' => 2, 'design_name' => 'Design 2', 'split_percent' => 25],
			2 => ['design_id' => 3, 'design_name' => 'Design 3', 'split_percent' => 25],
		];
	}

	// Check if the data set given have the sum of "split_percent" equals to 100
	// All A/B testing should sum to 100.
	private function isQuestionFiveDataInvalid($data) {
		$sum = 0;
		foreach($data as $d) {
			$sum += $d['split_percent'];
		}
		return $sum !== 100;
	}
}

// Initiate the Exads class
$test = new Exads();

// For iterative shell: Select the number of the question to run
$number = 0;
while($number <= 0 || $number > 6 || gettype($number) !== 'integer') {
	echo 'Insert the number of the exercise to execute: ';
	fscanf(STDIN, "%d\n", $number);
}
switch($number) {
	case 1: return $test->questionOne();
	case 2: return $test->questionTwo();
	case 3: return $test->questionThree();
	case 4: return $test->questionFour();
	case 5: return $test->questionFive();
	case 6: return $test->questionFiveStressTest();
}