<?php

putenv('GDFONTPATH=' . realpath('.'));

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
}

// Set the size of the board
$boardSize = 4;
$cellSize = 70;

// Create an empty image
$imageWidth = 573;
$imageHeight = 300;
$image = imagecreate($imageWidth, $imageHeight);

// Set background color
$backgroundColor = imagecolorallocate($image, 255, 253, 247);
imagefill($image, 0, 0, $backgroundColor);

// Set cell border color
$borderColor = imagecolorallocate($image, 10, 35, 66);

$font = 'Rubik-Regular'; 

// Draw the grid
for ($i = 0; $i < $boardSize; $i++) {
    for ($j = 0; $j < $boardSize; $j++) {
        $x1 = 10 + $j * $cellSize;
        $y1 = 10 + $i * $cellSize;
        $x2 = 10 + ($j + 1) * $cellSize;
        $y2 = 10 + ($i + 1) * $cellSize;

        // Draw the cell border
        imagerectangle($image, $x1, $y1, $x2, $y2, $borderColor);

        // Check if there is a value other than 0 at this cell
        $index = $i * 4 + $j; 
        if($boardArray[$index]!=0) { 
          $p = imagettfbbox(12, 0, $font, $boardArray[$index]);
          $halfwidth = intval(($p[2]-$p[0])/2); 
          imagettftext($image, 12, 0, $x1 + 35 - $halfwidth, $y1 + 42, $borderColor, $font, $boardArray[$index]); 
        }
    }
}

// Draw the title 
$fontColor = imagecolorallocate($image, 132, 101, 203);
$font = 'Rubik-Black'; // Change the font path as needed
imagettftext($image, 24, 0, 309, 36, $fontColor, $font, '2048 Game');

// Save the image to a file (you can also use imagepng, imagejpeg, etc.)
imagepng($image, '2048_board.png');

// Ignore
/* 
$identifier = implode('-',$boardArray); 
imagepng($image, 'b'.$identifier.'png');
*/

// Output the image to the browser
header('Content-Type: image/png');
imagepng($image);

// Free up memory
imagedestroy($image);

?>