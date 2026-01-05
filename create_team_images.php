<?php
// Create placeholder team member images
$team_images = [
    'person1.jpg' => ['name' => 'Mohamed Bangura', 'title' => 'CEO & Founder'],
    'person2.jpg' => ['name' => 'Fatmata Koroma', 'title' => 'Head of Scouting'],
    'person3.jpg' => ['name' => 'David Johnson', 'title' => 'Legal Advisor']
];

foreach ($team_images as $filename => $info) {
    $path = 'images/' . $filename;
    
    if (!file_exists($path)) {
        // Create image
        $width = 400;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);
        
        // Set colors
        $bg_color = imagecolorallocate($image, 26, 54, 93); // Primary blue
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $accent_color = imagecolorallocate($image, 45, 90, 39); // Accent green
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);
        
        // Add gradient effect
        for ($i = 0; $i < 100; $i++) {
            $x = rand(0, $width);
            $y = rand(0, $height);
            $color = imagecolorallocatealpha($image, 45, 90, 39, rand(20, 60));
            imagefilledellipse($image, $x, $y, rand(50, 150), rand(50, 150), $color);
        }
        
        // Add person silhouette
        $silhouette_color = imagecolorallocate($image, 33, 37, 41);
        imagefilledellipse($image, $width/2, 150, 120, 120, $silhouette_color); // Head
        imagefilledrectangle($image, $width/2 - 40, 210, $width/2 + 40, 350, $silhouette_color); // Body
        
        // Add text
        imagestring($image, 5, $width/2 - 70, 380, $info['name'], $text_color);
        imagestring($image, 3, $width/2 - 60, 410, $info['title'], $accent_color);
        
        // Save image
        if (imagejpeg($image, $path, 90)) {
            echo "Created: $path<br>";
        } else {
            echo "Failed to create: $path<br>";
        }
        
        imagedestroy($image);
    } else {
        echo "Already exists: $path<br>";
    }
}

echo "<br>✅ Team images created!<br>";
echo '<a href="about.php">View About Page</a>';
?>