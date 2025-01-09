<?php
function getConnection(){
  $host = '127.0.0.1';
  $db_name = 'pert4_todos';
  $username = 'root';
  $password = '';
  $conn = new mysqli($host, $username, $password, $db_name);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  return $conn;
}