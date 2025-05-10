<!DOCTYPE html>
<html>
<head>
    <title>Test Blood Delivery Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Test Blood Delivery Form</h1>
    
    <form action="process_delivery.php" method="POST" id="deliveryForm">
        <div class="form-group">
            <label for="blood_type">Blood Type:</label>
            <select name="blood_type" id="blood_type" required>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="blood_group">Blood Group:</label>
            <select name="blood_group" id="blood_group" required>
                <option value="whole_blood">Whole Blood</option>
                <option value="packed_red_cells">Packed Red Cells</option>
                <option value="fresh_frozen_plasma">Fresh Frozen Plasma</option>
                <option value="platelet_concentrate">Platelet Concentrate</option>
                <option value="cryoprecipitate">Cryoprecipitate</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="number_of_bags">Number of Bags:</label>
            <input type="number" name="number_of_bags" id="number_of_bags" min="1" value="1" required>
        </div>
        
        <div class="form-group">
            <label for="volume_ml">Volume (ml):</label>
            <input type="number" name="volume_ml" id="volume_ml" min="1" value="450">
        </div>
        
        <div class="form-group">
            <label for="collection_date">Collection Date:</label>
            <input type="date" name="collection_date" id="collection_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="expiration_date">Expiration Date:</label>
            <input type="date" name="expiration_date" id="expiration_date" value="<?php echo date('Y-m-d', strtotime('+42 days')); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="Available">Available</option>
                <option value="Reserved">Reserved</option>
                <option value="Used">Used</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="collected_by">Collected By:</label>
            <input type="text" name="collected_by" id="collected_by" value="Test User" required>
        </div>
        
        <div class="form-group">
            <label for="hospital_id">Hospital ID:</label>
            <input type="number" name="hospital_id" id="hospital_id" value="1" required>
        </div>
        
        <button type="submit">Submit</button>
    </form>
    
    <div class="result" id="result" style="display: none;"></div>
    
    <script>
        // JavaScript to handle form submission via AJAX
        document.getElementById('deliveryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            fetch('process_delivery.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                var resultDiv = document.getElementById('result');
                resultDiv.innerHTML = data;
                resultDiv.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                var resultDiv = document.getElementById('result');
                resultDiv.innerHTML = 'Error: ' + error;
                resultDiv.style.display = 'block';
            });
        });
        
        // Auto-calculate volume based on number of bags
        document.getElementById('number_of_bags').addEventListener('input', function() {
            var bags = parseInt(this.value) || 0;
            document.getElementById('volume_ml').value = bags * 450;
        });
    </script>
</body>
</html> 