<?php
require 'db_agq.php';




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Document View</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
          background-color: #f9f9f9;
          font-family: 'IBM Plex Sans', Arial, Helvetica;
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          margin: 0;
        }

        .container {
          background-color: #90ae5e;
          margin: 50px 75px 50px 75px;
          border-radius: 15px;
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: space-around;
          height: 800px;
          width: 1300px;
          border-width: 20px;
          border-color: #73894e;
          border-style: solid;
        }

        .document-view {
          background-color: #d0dcb3;
          height: 700px;
          width: 600px;
          padding: 50px 100px 50px 100px;
          margin: 10px;
          border-radius: 10px;
        }

        .info-view {
          display: flex;
          flex-direction: column;
          padding: 50px 50px 50px 30px; 
          justify-content: flex-start;
          height: 100%;
          width: 650px; 
        }

        .docu-information {
          margin-bottom: 225px;
          padding-top: 0px;
        }

        .ref-number {
          font-size: 50px;
          font-weight: bold;
          margin-bottom: 10px;
        }
        
        .date {
          font-size: 20px;
        }

        .document-type {
          font-size: 22px;
        }

        .comment-title {
          font-size: 17px;
          font-weight: bold;
        }
        
        textarea {
          width: 100%;
          height: 150px;
          padding: 10px;
          font-size: 16px;
          resize: none;
          box-sizing: border-box;
          margin-bottom: 0px;
          border-radius: 15px;
        }
        
        .counter {
          text-align: left;
          font-size: 14px;
          color: black;
          margin-left: 5px;
          margin-bottom: 15px;
          display: inline-block; 
 
        }


        .save-button {
          background-color: #62851b;
          
          padding: 10px 15px;
          border: none;
          border-radius: 50px;
          font-family: 'IBM Plex Sans', Arial, sans-serif;
          font-weight: bold;
          cursor: pointer;
          box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
          transition: background-color 0.3s ease;  
        }
        
        .save-button:hover {
            background-color: #5a6e3a;
        }
        
        .button-container {
            float: right; 
            margin-top: 5px; 
        }

        /* Responsive styles */
        @media (max-width: 1400px) {
            .container {
                width: 90%;
                margin: 30px;
                gap: 30px;
            }
            
            .info-view {
                padding: 30px 50px;
            }
            
            .document-view {
                width: 50%;
                padding: 30px 50px;
            }
        }
        
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
                height: auto;
                gap: 20px;
            }
            
            .document-view {
                width: 90%;
                height: 500px;
                margin-top: 30px;
            }
            
            .info-view {
                width: 90%;
                margin-bottom: 30px;
            }
            
            .docu-information {
                margin-bottom: 50px;
                padding-top: 0;
            }
        }
    </style>
</head>
<body>
  <div class="container">
    <div class="document-view">
    </div>
    <div class="info-view">
      <div class="docu-information">
        <p class="ref-number">IB-0000</p>
        <p class="date"><strong>Date:</strong> 01/01/2025</p>
        <p class="document-type">Statement of Account</p>
      </div>
      <span class = "comment-title"> Comments: </span>
      <div class="comment-box">
        <textarea id="textbox" maxlength="250" oninput="updateCounter()"></textarea>
        <div class="counter" id="counter">0/250</div>
        <div class="button-container">
          <button class="save-button" onclick="saveComment()">Save</button>
        </div>
      </div>
    </div>   
  </div>
  <script>
     function updateCounter() {
          let textbox = document.getElementById("textbox");
          let counter = document.getElementById("counter");
          let used = textbox.value.length;
          counter.textContent = used + "/250";
      }
      
     function saveComment() {
          let comment = document.getElementById("textbox").value;
          alert("Comment saved: " + comment);
          
     }
  </script>
</body>
</html>