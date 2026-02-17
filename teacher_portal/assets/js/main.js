// assets/js/main.js
document.addEventListener('DOMContentLoaded', () => {
    console.log('TeacherConnect Pro JS loaded');
    const loginBtn = document.querySelector('#login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.querySelector('#login-form');
            if (form.checkValidity()) {
                form.submit();
            } else {
                form.reportValidity();
            }
        });
    }
    const registerBtn = document.querySelector('#register-btn');
    if (registerBtn) {
        registerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.querySelector('#register-form');
            if (form.checkValidity()) {
                form.submit();
            } else {
                form.reportValidity();
            }
        });
    }
});
function toggleLike(postId) {
    fetch('api.php?action=like&post_id=' + postId, {method: 'POST'}).then(res => res.json()).then(data => {
        document.getElementById('likes-' + postId).textContent = data.likes_count;
    });
}

function quickApply(jobId) {
    fetch('api.php?action=apply&job_id=' + jobId, {method: 'POST'}).then(res => res.json()).then(data => {
        alert('Applied successfully!');
    });
}

function previewMedia(input) {
    const preview = document.getElementById('media-preview');
    preview.innerHTML = '';
    if (input.files[0]) {
        const url = URL.createObjectURL(input.files[0]);
        if (input.files[0].type.includes('video')) {
            const video = document.createElement('video');
            video.src = url;
            video.controls = true;
            preview.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = url;
            preview.appendChild(img);
        }
    }
}

// Attach to file input
document.querySelector('input[name="media"]').addEventListener('change', e => previewMedia(e.target));