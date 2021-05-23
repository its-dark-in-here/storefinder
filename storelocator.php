<?php
ini_set('display_errors', 1);
require("./phpsqlsearch_dbinfo.php");
// Get parameters from URL
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
$prodtype = $_GET["prodtype"];
$prodvalue = urldecode($_GET["prodvalue"]);
// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);
// Opens a connection to a mySQL server
if (!$mysqli = new mysqli($hostname, $username, $password, $database))
{
  echo mysqli_connect_error();
}


// > $connection=mysql_connect ($hostname, $username, $password);
// > if (!$connection) {
// >   die("Not connected : " . mysql_error());
// > }


// Set the active mySQL database
// > $db_selected = mysql_select_db($database, $connection);
// > if (!$db_selected) {
// >   die ("Can\'t use db : " . mysql_error());
// > }


// Search the rows in the markers table  NB: 3959 is radius of Earth in miles. For KM set this to 6371
//$query = "SELECT uniqueid, storename, storeaddress1, latitude, longitude, ( 3959 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance FROM retailerlocations HAVING distance < ? ORDER BY distance LIMIT 0 , 20;";

// $query = "SELECT uniqueid, retailername, Replace(concat('<B>',StoreName,'</B><BR>',StoreAddress1,'<BR>',StoreAddress2,'<BR>',StoreTown,'<BR>',StorePostCode),'<BR><BR>','<BR>') AS storeaddress1, latitude, longitude, ( 3959 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance FROM retailerlocations WHERE EXISTS (SELECT 1 FROM productlistings WHERE retailerlocations.retailerstorenumber =  productlistings.retailerstorenumber AND retailerlocations.retailername = productlistings.retailername AND productlistings.BrandRange=?) HAVING distance < ? ORDER BY distance LIMIT 0 , 20;";

$query = "SELECT uniqueid, concat(retailername,' - ',StoreName) AS retailername, Replace(concat('<B>',StoreAddress1,'<BR>',StoreAddress2,'<BR>',StoreTown,'<BR>',StorePostCode),'<BR><BR>','<BR>') AS storeaddress1, ";
$query = $query . " latitude, longitude, ( 3959 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance ";
$query = $query . " FROM retailerlocations ";
$query = $query . " WHERE EXISTS (SELECT 1 FROM productlistings WHERE retailerlocations.retailerstorenumber = productlistings.retailerstorenumber";
$query = $query . " AND retailerlocations.retailername = productlistings.retailername AND productlistings.Brand=?)";
$query = $query . " HAVING distance < ? ORDER BY distance LIMIT 0 , 20;";

$stmt = $mysqli->prepare($query);
if (!$stmt)
{
  echo "SQL ERROR"; // handle error
}

$stmt->bind_param('dddsi',$center_lat,$center_lng,$center_lat,$prodvalue,$radius);	    // Bind parameters
// $stmt->bind_param('dddi',$center_lat,$center_lng,$center_lat,$radius);	    // Bind parameters
if (!$stmt->execute())
{
  echo "SQL ERROR"; // handle error
}						// Execute the prepared query
$result = $stmt->get_result();

// > $query = sprintf("SELECT uniqueid, storename, storeaddress1, latitude, longitude, ( 3959 * acos( cos( radians('%s') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( latitude ) ) ) ) AS distance FROM retailerlocations HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
// >   mysql_real_escape_string($center_lat),
// >   mysql_real_escape_string($center_lng),
// >   mysql_real_escape_string($center_lat),
// >   mysql_real_escape_string($radius));
// > //$result = mysql_query($query);

// > $result = mysql_query($query);
// > if (!$result) {
// >   die("Invalid query: " . mysql_error());
// > }

// echo "numrows: " . $result->num_rows;

if ($result->num_rows >= "1") {
  header("Content-type: text/xml");
  // Iterate through the rows, adding XML nodes for each
// >   while ($row = @mysql_fetch_assoc($result)){
// >     $node = $dom->createElement("marker");
// >     $newnode = $parnode->appendChild($node);
// >     $newnode->setAttribute("id", $row['uniqueid']);
// >     $newnode->setAttribute("name", $row['storename']);
// >     $newnode->setAttribute("address", $row['storeaddress1']);
// >     $newnode->setAttribute("lat", $row['latitude']);
// >     $newnode->setAttribute("lng", $row['longitude']);
// >     $newnode->setAttribute("distance", $row['distance']);
// >   }

// echo "PRE WHILE: ";

  while ($row = $result->fetch_assoc()) {
    // echo "In WHILE";
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("id", $row['uniqueid']);
    $newnode->setAttribute("name", $row['retailername']);
    $newnode->setAttribute("address", $row['storeaddress1']);
    $newnode->setAttribute("lat", $row['latitude']);
    $newnode->setAttribute("lng", $row['longitude']);
    $newnode->setAttribute("distance", $row['distance']);
  }
  // echo "POST WHILE: ";

  /* free result set */
  $result->free();

  echo $dom->saveXML();

}
else {
  printf("Errormessage: %s\n", $mysqli->error);
}
$mysqli->close();

// >
// > END
?>
