document.addEventListener("DOMContentLoaded", function () {
    const postsContainer = document.getElementById('posts-container');

    fetch('/fishated/test.php')
        .then(response => response.json())
        .then(posts => {
            if (posts.length === 0) {
                postsContainer.innerHTML = '<p>No posts available.</p>';
                return;
            }

            const groupedPosts = posts.reduce((groups, post) => {
                if (!groups[post.category]) {
                    groups[post.category] = [];
                }
                groups[post.category].push(post);
                return groups;
            }, {});

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
            postsContainer.innerHTML = '<p>Error loading posts.</p>';
        });
});
