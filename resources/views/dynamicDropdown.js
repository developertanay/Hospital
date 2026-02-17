// dynamicDropdown.js
<?php
function updateSubjectDropdown(subjectTypeId, selectedValue) {
    const endpoint = `/api/getSubjects?subjectTypeId=${subjectTypeId}&selectedValue=${selectedValue}`;

    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            const subjectDropdown = document.getElementById('subject');
            subjectDropdown.innerHTML = '';

            data.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                subjectDropdown.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}
?>
