document.addEventListener("DOMContentLoaded", function () {
    const postsContainer = document.getElementById('posts-container');

    fetch('/fetch_posts.php')
        .then(response => {
            // Check if the response is OK (status code 200-299)
            if (!response.ok) {
                // If not OK, throw an error with the status text
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            // Parse the response as JSON
            return response.json(); 
        })
        .then(posts => {
            if (posts.length === 0) {
                postsContainer.innerHTML = '<p>No posts available.</p>';
                return;
            }

            for (const category in groupedPosts) {
                const categoryDiv = document.createElement('div');
                categoryDiv.classList.add('category-group');
                const categoryTitle = document.createElement('h3');
                categoryTitle.textContent = category;
                categoryDiv.appendChild(categoryTitle);

                groupedPosts[category].forEach(post => {
                    const postLink = document.createElement('a');
                    postLink.href = post.path;
                    postLink.textContent = post.title;
                    categoryDiv.appendChild(postLink);
                });

                postsContainer.appendChild(categoryDiv);
            }
        })
        .catch(error => {
            console.error('Error fetching posts:', error);
            postsContainer.innerHTML = '<p>Error loading posts. Please try again later.</p>';

            // Additional debugging: Log the full response text to the console
            error.response.text().then(text => {
                console.error('Response text:', text); 
            });
        });
});
