let songId;
let score;
const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

//get all the ten stars elements inside the modal dialog
const starsElements = document.getElementsByClassName('star-hoverable');

//get all rate dialog buttons
const rateDialogButtons = document.getElementsByClassName('rateDialogBtn');


//on click the 'Rate!' button
Array.from(rateDialogButtons).forEach(function(element) {
    element.addEventListener('click', function(e){
        songId = e.target.getAttribute('data-id');
        console.log(songId);
    });
});


//display the score when hovering the stars icons inside the modal dialog (hover/mouseover on stars icons event)
Array.from(starsElements).forEach(function(element) {
    element.addEventListener('mouseover', function(e){
        document.getElementById('score-display').textContent = `(${e.target.getAttribute('data-score')}/10)`;
    });
});



//on click the stars icon inside the dialog
document.querySelector("#rateDialog").addEventListener('click', function(e){

    if (e.target.classList.contains("star-hoverable")){    

        //get the score of the active clicked star
        score = Number(e.target.getAttribute('data-score'));
     
        //select the active ones
        for (let i = 0; i<10; i++){
            if (Number(starsElements[i].getAttribute('data-score')) <= score){
                starsElements[i].classList.add('active');
            }else{
                starsElements[i].classList.remove('active');
            }
        }

        //display the alert panel
        const alertPanel = document.getElementById('dialog-alert');
        alertPanel.style = "display: block";
        
        //loading spinner
        alertPanel.innerHTML = `<div class="spinner-border text-primary text-center" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>`;

        fetch(`${baseUrl}/songs/${songId}/rate/${score}`)
            .then(response => response.json())
            .then((data)=> { 
            console.log(data);
            alertPanel.innerHTML = `<div class="fw-bold">${data.message}</div>
                <div>Your rating: <strong>${data.ratingSaved}</strong></div>
                <div>Number of all ratings for this song: <strong>${data.ratingsCount}</strong></div>
                <div>Average rating: <strong>${data.avgRating}</strong></div>`;
            
        });
    }
});




//reload on close
document.getElementById('btn-close').addEventListener('click', () => window.location.reload());
document.getElementById('btn-ok').addEventListener('click',  ()=> window.location.reload());
