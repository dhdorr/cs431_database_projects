<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!DOCTYPE html>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <style>
    .card-img-top {
      width: 100%;
      height: 40vh;
      object-fit: cover;
    }
  </style>
  <?php
  $sortBy = "Select one";
  #Photo class so each photo's info can be saved to an array
  class Photo
  {
    public $name;
    public $date;
    public $location;
    public $photographer;
    public $photo_file;

    function set_name($name)
    {
      $this->name = $name;
    }
    function get_name()
    {
      return $this->name;
    }
    function set_date($date)
    {
      $this->date = $date;
    }
    function get_date()
    {
      return $this->date;
    }
    function set_location($location)
    {
      $this->location = $location;
    }
    function get_location()
    {
      return $this->location;
    }
    function set_photographer($photographer)
    {
      $this->photographer = $photographer;
    }
    function get_photographer()
    {
      return $this->photographer;
    }
    function set_photo_file($photo_file)
    {
      $this->photo_file = $photo_file;
    }
    function get_photo_file()
    {
      return $this->photo_file;
    }
  }
  //connect to database
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "cs431s47";
  //creat connection
  $db = mysqli_connect($servername, $username, $password, $database);
  //check connection
  if (!$db) {
    die("connection failed" . mysqli_connect_error());
  }
  if (isset($_POST["pname"])) {
    // create short variable names
    $pname = trim($_POST["pname"]);
    $date = trim($_POST["date"]);
    $location = trim($_POST["location"]);
    $photoer = trim($_POST["photoer"]);
    $filename = basename($_FILES["uploadfile"]["name"]);

    #Form handling & checks if image is ok to upload
    $dir = "uploads/";
    $file = $dir . basename($_FILES["uploadfile"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    #check if uploadfile is jepg/png
    $info = getimagesize($_FILES['uploadfile']['tmp_name']);

    if ($info === FALSE) {
      $uploadOk = 0;
    }
    if (($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
      $uploadOk = 0;
    }

    #checks if the upload is ok
    if ($uploadOk == 0) {
      echo "File failed to upload";
    } else {
      if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $file)) {
        echo "upload success";
      } else {
        echo "File failed to upload";
      }
    }

    //insert image meta data into database
    if ($uploadOk == 1) {
    $query = "INSERT INTO images (filename, photoname, date, location, photographer ) VALUES (?,?,?,?,?) ";
    $stmt = $db->prepare($query);
    $stmt->bind_param('sssss', $filename, $pname, $date, $location,  $photoer);
    $stmt->execute();
    }
  }
  //save data from database to object array
  $pic_array = array();
  // Sorting using SQL statement
  if (isset($_GET['sortBy'])) {
    $sortBy = $_GET['sortBy'];
    sortInSQL($sortBy, $db);
    //usort($pic_array, 'comparator');
  }
  else {
    $query = "SELECT * FROM images";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($data = $result->fetch_assoc()) {
      $pic = new Photo();
      $pic->set_photo_file($data["filename"]);
      $pic->set_name($data["photoname"]);
      $pic->set_date($data["date"]);
      $pic->set_location($data["location"]);
      $pic->set_photographer($data["photographer"]);
      if ($pic->get_photo_file() != null) {
        array_push($pic_array, $pic);
      }
    }
  }

  function sortInSQL($sortParam, $db)
  {
    switch ($sortParam) {
      case 'Name':
        $query = "SELECT * FROM images ORDER BY photoname DESC";
        break;
      case "Date":
        $query = "SELECT * FROM images ORDER BY date DESC";
        break;
      case "Photographer":
        $query = "SELECT * FROM images ORDER BY photographer DESC";
        break;
      case "Location":
        $query = "SELECT * FROM images ORDER BY location DESC";
        break;
    }
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($data = $result->fetch_assoc()) {
      $pic = new Photo();
      $pic->set_photo_file($data["filename"]);
      $pic->set_name($data["photoname"]);
      $pic->set_date($data["date"]);
      $pic->set_location($data["location"]);
      $pic->set_photographer($data["photographer"]);
      if ($pic->get_photo_file() != null) {
        array_push($pic_array, $pic);
      }
    }
  }

  // Comparator function used for comparator
  function comparator($object1, $object2)
  {
    $sortBy = $_GET['sortBy'];
    switch ($sortBy) {
      case 'Name':
        return strcmp($object1->get_name(), $object2->get_name());
        break;
      case "Date":
        return strcmp(strtotime($object1->get_date()), strtotime($object2->get_date()));
        break;
      case "Photographer":
        return strcmp($object1->get_photographer(), $object2->get_photographer());
        break;
      case "Location":
        return strcmp($object1->get_location(), $object2->get_location());
        break;
    }
  }

  ?>
</head>

<body>
  <br><br>

  <div class="container">
    <div class="row">
      <div class="col">
        <h1>View All Photos</h1>
      </div>
    </div>

    <br>

    <div class="row justify-content-start">
      <div class="col-4">
        <div class="input-group mb-0">
          <div class="input-group-prepend">
            <label class="input-group-text" for="inputGroupSelect01">Sort By:</label>
          </div>
          <!-- dropdown button for sort -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?php echo $sortBy ?>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="?sortBy=Name" id=sortname>Name</a>
              <a class="dropdown-item" href="?sortBy=Date" id=sorttime>Date </a>
              <a class="dropdown-item" href="?sortBy=Photographer" id=sortphotoer> Photographer</a>
              <a class="dropdown-item" href="?sortBy=Location" id=sortlocation>Location</a>
            </div>
          </div>
        </div>
      </div>
      <!-- upload button  -->
      <div class="col-2 offset-6">
        <a href="index.html" class="btn btn-primary btn-md active" role="button" aria-pressed="true">Upload Photo</a>
      </div>
    </div>


    <br>
    <!-- use foreach loop to display all image in uploads folder -->
    <div class="card-deck">
      <?php foreach ($pic_array as $pic_array) : ?>

        <div class="col-4">
          <div class="card">
            <img class="card-img-top" src=<?php echo "uploads/" . $pic_array->get_photo_file(); ?> alt="card image cap">
            <div class="card-body">
              <h5 class="card-title"><?php echo $pic_array->get_name(); ?></h5>
              <p class="card-text"><?php echo $pic_array->get_date(); ?><br><?php echo $pic_array->get_location(); ?><br><?php echo $pic_array->get_photographer(); ?></p>
            </div>
          </div>
        </div>

        <br>
      <?php endforeach; ?>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>

</html>
