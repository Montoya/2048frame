<?php 

function quit($msg) { 
  // Send a 500 Internal Server Error header
  header('HTTP/1.1 500 Internal Server Error');
  echo $msg; 
  exit; 
}

function isValidBoard($input) {
  // Use a regular expression to check if the input contains only numbers (0-9) and commas
  return preg_match('/^[0-9,]+$/', $input);
}

function unZeroArray($arr) { 
	return array_filter($arr, function ($value) {
    return $value !== 0;
	});
}

function moveRight(&$board) { 
	for($i = 0; $i < 16; $i++) { 
		if(0 == ($i % 4)) { 
			$row = [$board[$i], $board[$i+1], $board[$i+2], $board[$i+3]]; 
			// make a new row with all numbers moved to the right
			$filtered = unZeroArray($row); 
			$zeros = array_fill(0, 4-count($filtered), 0);
			$newRow = array_merge($zeros,$filtered); 
			$board[$i] = $newRow[0]; 
			$board[$i+1] = $newRow[1]; 
			$board[$i+2] = $newRow[2]; 
			$board[$i+3] = $newRow[3]; 
		}
	}
}

function moveLeft(&$board) { 
	for($i = 0; $i < 16; $i++) { 
		if(0 == ($i % 4)) { 
			$row = [$board[$i], $board[$i+1], $board[$i+2], $board[$i+3]]; 
			// make a new row with all numbers moved to the right
			$filtered = unZeroArray($row); 
			$zeros = array_fill(0, 4-count($filtered), 0);
			$newRow = array_merge($filtered,$zeros); 
			$board[$i] = $newRow[0]; 
			$board[$i+1] = $newRow[1]; 
			$board[$i+2] = $newRow[2]; 
			$board[$i+3] = $newRow[3]; 
		}
	}
}

function moveUp(&$board) { 
	for($i = 0; $i < 4; $i++) { 
		$col = [$board[$i], $board[$i+4], $board[$i+8], $board[$i+12]]; 

		$filtered = unZeroArray($col); 
		$zeros = array_fill(0, 4-count($filtered), 0);
		$newCol = array_merge($filtered,$zeros); 
		$board[$i] = $newCol[0]; 
		$board[$i+4] = $newCol[1]; 
		$board[$i+8] = $newCol[2]; 
		$board[$i+12] = $newCol[3]; 
	}
}

function moveDown(&$board) { 
	for($i = 0; $i < 4; $i++) { 
		$col = [$board[$i], $board[$i+4], $board[$i+8], $board[$i+12]]; 

		$filtered = unZeroArray($col); 
		$zeros = array_fill(0, 4-count($filtered), 0);
		$newCol = array_merge($zeros,$filtered); 
		$board[$i] = $newCol[0]; 
		$board[$i+4] = $newCol[1]; 
		$board[$i+8] = $newCol[2]; 
		$board[$i+12] = $newCol[3]; 
	}
}

function combineRowLeft(&$board) {
	for ($i = 0; $i < 15; $i++) {
		if( ( $i + 1 ) % 4 !== 0 ) { 
			if( $board[$i] === $board[$i+1] ) { 
				$total = $board[$i] + $board[$i+1]; 
				$board[$i] = $total; 
				$board[$i+1] = 0; 
			}
		}
	}
}

function combineRowRight(&$board) {
	for ($i = 15; $i > 0; $i--) {
		if( $i % 4 !== 0 ) { 
			if( $board[$i] === $board[$i-1] ) { 
				$total = $board[$i] + $board[$i-1]; 
				$board[$i] = $total; 
				$board[$i-1] = 0; 
			}
		}
	}
}

function combineColumnUp(&$board) {
	for ($i = 0; $i < 12; $i++) {
		if( $board[$i] === $board[$i+4] ) {
			$total = $board[$i] + $board[$i+4]; 
			$board[$i] = $total; 
			$board[$i+4] = 0; 
		}
	}
}

function combineColumnDown(&$board) {
	for ($i = 15; $i > 3; $i--) {
		if( $board[$i] === $board[$i-4] ) {
			$total = $board[$i] + $board[$i-4]; 
			$board[$i] = $total; 
			$board[$i-4] = 0; 
		}
	}
}

function placeRandomTile(&$board) {
	// Find empty positions on the board
	$emptyPositions = array_keys($board, 0);

	if (empty($emptyPositions)) {
		// No empty positions left
		return;
	}

	// Choose a random empty position
	$randomPosition = $emptyPositions[array_rand($emptyPositions)];

	// Place a random tile (2 or 4) at the chosen position
	$board[$randomPosition] = (mt_rand(0, 5) == 0) ? 4 : 2;
}

$boardArray = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];  
if(!empty($_GET['b'])) { 
  // Create board from GET parameters
  $boardString = $_GET['b']; 
  if(!isValidBoard($boardString)) { 
    quit("500 Internal Server Error - Something went wrong."); 
  }
  $boardArray = explode(',',$boardString); 
  if(count($boardArray)!=16) { 
    quit("500 Internal Server Error - Something went wrong."); 
  }
	$boardArray = array_map('intval', $boardArray);
}

// if the board is empty, create two random 2s and be done with it
if(0==array_sum($boardArray)) { 
	$keys = array_keys($boardArray); 
	shuffle($keys); 
	$boardArray[$keys[0]] = 2; 
	$boardArray[$keys[1]] = 2; 
}
else { 
	// look for an action from the user and respond to it 
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		try { 
			$jsonData = file_get_contents('php://input');
			$data = json_decode($jsonData, true);  

			$btnIndex = intval($data['untrustedData']['buttonIndex']); 

			switch($btnIndex) { 
				case 1: 
					moveUp($boardArray); 
					combineColumnUp($boardArray); 
					moveUp($boardArray); 
					break; 
				case 2: 
					moveRight($boardArray); 
					combineRowRight($boardArray); 
					moveRight($boardArray); 
					break; 
				case 3: 
					moveDown($boardArray); 
					combineColumnDown($boardArray); 
					moveDown($boardArray); 
					break; 
				case 4: 
					moveLeft($boardArray); 
					combineRowLeft($boardArray); 
					moveLeft($boardArray); 
					break; 
				default: 
					break; 
			}
		}
		catch(Exception $e) { 
			quit($e); 
		}
	}

	// place a random tile 
	placeRandomTile($boardArray); 
}
/*
$filePath = 'example.txt';

// Write data to the file
file_put_contents($filePath, $progress); 
*/

$boardString = implode(',',$boardArray); 
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Playing 2048</title>
		<meta property="og:title" content="Playing 2048" />
		<meta property='og:image' content="https://homerow.club/2048/i.php?b=<?=$boardString?>" />
		<meta property="fc:frame" content="vNext" />
		<meta property="fc:frame:image" content="https://homerow.club/2048/i.php?b=<?=$boardString?>" />
		<meta property="fc:frame:button:1" content="⬆️" />
		<meta property="fc:frame:button:2" content="➡️" />
		<meta property="fc:frame:button:3" content="⬇️" />
		<meta property="fc:frame:button:4" content="⬅️" />
		<meta property="fc:frame:post_url" content="https://homerow.club/2048/play.php?b=<?=$boardString?>" />
		<link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
      <style type="text/css">
        body { 
          display:flex;
          justify-content:center;
          align-items:center;
          height:100vh;
          background-color:#8465cb;
        }
        #page { 
          width:760px;
          background-color:#fff;
          border-radius:24px;
          padding:24px 36px;
        }
        h1+p { 
          margin-top:-1em;
          opacity:0.7; 
          font-weight:700; 
        }
      </style>
	</head>
	<body>
		<div id="page">
      <h1>2048 Frame</h1>
      <p>Fully playable 2048 in a Farcaster Frame</p>
      <p>Want to see the code? Go to: <a href="https://github.com/Montoya/2048frame/">github.com/Montoya/2048frame/</a></p>
      <p>It's open source (MIT License), feel free to use it for your own projects.</p>
    </div>
	</body>
</html>