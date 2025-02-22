function loadContent(contentType) {
    if (contentType === 'bloodRequests') {
        fetch('process_history_request.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('bloodRequestsContainer').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }
}



function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}
// Modal function (as referenced in Make Request)
function openModal() {
    alert('Open Modal to Make Request');
}

// Function to open the modal
function openModal() {
    document.getElementById('myModal').style.display = "block";
}

// Function to close the modal
function closeModal() {
    document.getElementById('myModal').style.display = "none";
}

// Function to show the corresponding unit input field based on the selected request type
function showUnits() {
    // Hide all unit input fields
    var allUnitDivs = document.querySelectorAll('[id$="Units"]');
    allUnitDivs.forEach(function(div) {
        div.style.display = "none";
    });

    // Get the selected request type
    var selectedRequestType = document.getElementById('requestType').value;

    // Show the corresponding unit input field
    if (selectedRequestType === "wholeBlood") {
        document.getElementById('wholeBloodUnits').style.display = "block";
    } else if (selectedRequestType === "packedRBC") {
        document.getElementById('packedRBCUnits').style.display = "block";
    } else if (selectedRequestType === "buffyCoatPoorRBC") {
        document.getElementById('buffyCoatPoorRBCUnits').style.display = "block";
    } else if (selectedRequestType === "plateletConcentrate") {
        document.getElementById('plateletConcentrateUnits').style.display = "block";
    } else if (selectedRequestType === "apheresisPlatelets") {
        document.getElementById('apheresisPlateletsUnits').style.display = "block";
    } else if (selectedRequestType === "leukocytePoorPlatelets") {
        document.getElementById('leukocytePoorPlateletsUnits').style.display = "block";
    } else if (selectedRequestType === "freshFrozenPlasma") {
        document.getElementById('freshFrozenPlasmaUnits').style.display = "block";
    } else if (selectedRequestType === "leukocytePoorFreshFrozenPlasma") {
        document.getElementById('leukocytePoorFreshFrozenPlasmaUnits').style.display = "block";
    } else if (selectedRequestType === "cryoprecipitate") {
        document.getElementById('cryoprecipitateUnits').style.display = "block";
    } else if (selectedRequestType === "washedRBC") {
        document.getElementById('washedRBCUnits').style.display = "block"; // Show the new field
    }
}

function showPlateletTypes() {
var selectedUnits = document.querySelector('input[name="apheresisPlateletsUnits"]:checked');
var plateletTypesDiv = document.getElementById("plateletTypeOptions");

// Show additional options if 4 or 8 units are selected
if (selectedUnits && (selectedUnits.value === "4" || selectedUnits.value === "8")) {
    plateletTypesDiv.style.display = "block";
} else {
    plateletTypesDiv.style.display = "none";
}
}

// Show the reason text box if WB2 is selected
document.querySelectorAll('input[name="wholeBloodType"]').forEach(radio => {
radio.addEventListener('change', function() {
if (this.value === 'WB2') {
    document.getElementById('reasonDiv').style.display = 'block';
} else {
    document.getElementById('reasonDiv').style.display = 'none';
}
});
});


document.querySelectorAll('input[name="packedRBCType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonRBCDiv = document.getElementById('reasonRBCDiv');

// Show reason text box only when R5 is selected
if (this.value === 'R5') {
    reasonRBCDiv.style.display = 'block'; // Show the reason box when R5 is selected
} else {
    reasonRBCDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});

// JavaScript to toggle the reason text box visibility for Washed RBC (WP4)
document.querySelectorAll('input[name="washedRBCType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonWashedRBCDiv = document.getElementById('reasonWashedRBCDiv');

// Show reason text box only when WP4 is selected
if (this.value === 'WP4') {
    reasonWashedRBCDiv.style.display = 'block'; // Show the reason box when WP4 is selected
} else {
    reasonWashedRBCDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});

// JavaScript to toggle the reason text box visibility for Buffy Coat-Poor RBC (WP4)
document.querySelectorAll('input[name="buffyRBCType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonBuffyRBCDiv = document.getElementById('reasonBuffyRBCDiv');

// Show reason text box only when WP4 is selected
if (this.value === 'WP4') {
    reasonBuffyRBCDiv.style.display = 'block'; // Show the reason box when WP4 is selected
} else {
    reasonBuffyRBCDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});
document.querySelectorAll('input[name="plateletConcentrateType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonPlateletConcentrateDiv = document.getElementById('reasonPlateletConcentrateDiv');

// Show reason text box only when P6 is selected
if (this.value === 'P6') {
    reasonPlateletConcentrateDiv.style.display = 'block'; // Show the reason box when P6 is selected
} else {
    reasonPlateletConcentrateDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});
// JavaScript to toggle the reason text box visibility for Apheresis Platelets (P6)
document.querySelectorAll('input[name="apheresisPlateletType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonApheresisPlateletsDiv = document.getElementById('reasonApheresisPlateletsDiv');

// Show reason text box only when P6 is selected
if (this.value === 'P6') {
    reasonApheresisPlateletsDiv.style.display = 'block'; // Show the reason box when P6 is selected
} else {
    reasonApheresisPlateletsDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});
// JavaScript to toggle the reason text box visibility for Leukocyte Poor Platelets (P6)
document.querySelectorAll('input[name="leukocytePoorPlateletType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonLeukocytePlateletsDiv = document.getElementById('reasonLeukocytePlateletsDiv');

// Show reason text box only when P6 is selected
if (this.value === 'P6') {
    reasonLeukocytePlateletsDiv.style.display = 'block'; // Show the reason box when P6 is selected
} else {
    reasonLeukocytePlateletsDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});
// JavaScript to toggle the reason text box visibility for Fresh Frozen Plasma (F5)
document.querySelectorAll('input[name="freshFrozenPlasmaType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonFreshFrozenPlasmaDiv = document.getElementById('reasonFreshFrozenPlasmaDiv');

// Show reason text box only when F5 is selected
if (this.value === 'F5') {
    reasonFreshFrozenPlasmaDiv.style.display = 'block'; // Show the reason box when F5 is selected
} else {
    reasonFreshFrozenPlasmaDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});
document.querySelectorAll('input[name="leukocytePoorFreshFrozenPlasmaType"]').forEach(function(radio) {
radio.addEventListener('change', function() {
var reasonleukocytePoorFreshFrozenPlasmaDiv = document.getElementById('reasonleukocytePoorFreshFrozenPlasmaDiv');

// Show reason text box only when F5 is selected
if (this.value === 'F5') {
    reasonleukocytePoorFreshFrozenPlasmaDiv.style.display = 'block'; // Show the reason box when F5 is selected
} else {
    reasonleukocytePoorFreshFrozenPlasmaDiv.style.display = 'none'; // Hide the reason box for other selections
}
});
});




