<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Category Sidebar -->
            <div class="col-md-3 col-lg-2 category-sidebar">
                <div id="category-list"></div>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10">
                <div class="page-header">
                    <h1 class="page-title" id="page-title">Course catalog</h1>
                </div>
                
                <div class="container">
                    <div class="row g-4" id="course-container">
                        <!-- Course cards will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store the data globally
        let categories = [];
        let courses = [];
        let currentCategory = null;
        let apiUrl = 'http://api.cc.localhost/index.php/';

        // Function to fetch categories
        async function fetchCategories() {
            try {
                const response = await fetch(`${apiUrl}categories`);
                categories = await response.json();
                console.log('Categories:', categories);
                renderCategories();
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        // Function to fetch courses
        async function fetchCourses() {
            try {
                const response = await fetch(`${apiUrl}courses`);
                courses = await response.json();
                console.log('Courses:', courses);
                // Render courses after fetching
                renderCourses();
            } catch (error) {
                console.error('Error fetching courses:', error);
            }
        }

        // Build category tree
        function buildCategoryTree() {
            const categoryMap = {};
            const rootCategories = [];
            
            // Create a map of categories by ID
            categories.forEach(category => {
                categoryMap[category.id] = { ...category, children: [] };
            });
            console.log('Category map:', categoryMap);
            // Build the hierarchy
            categories.forEach(category => {
                if (category.parent_id === null) {
                    rootCategories.push(categoryMap[category.id]);
                } else if (categoryMap[category.parent_id]) {
                    categoryMap[category.parent_id].children.push(categoryMap[category.id]);
                }
            });
            
            return rootCategories;
        }


        // Get all child categories (recursively)
        function getChildCategories(parentId) {
            const childIds = [];
            
            // Find direct children
            const directChildren = categories.filter(cat => cat.parent_id === parentId);
            
            directChildren.forEach(child => {
                childIds.push(child.id);
                // Recursively add grandchildren
                const grandchildIds = getChildCategories(child.id);
                childIds.push(...grandchildIds);
            });
            
            return childIds;
        }

        // Render categories in the sidebar
        function renderCategories() {
            const categoryTree = buildCategoryTree();
            const categoryList = document.getElementById('category-list');
            categoryList.innerHTML = '';
            console.log('Rendering categories:', categoryTree);
            // Render each root category
            categoryTree.forEach(category => {
                renderCategoryItem(category, categoryList, 0);
            });
        }

        // Render a single category item and its children
        function renderCategoryItem(category, container, level) {
            const courseCount = category.count_of_courses;
            
            // Create category item
            const categoryItem = document.createElement('div');
            categoryItem.className = `category-item ${level > 0 ? 'subcategory-item' : ''}`;
            if (level === 1) categoryItem.classList.add('subcategory-level2');
            if (level === 2) categoryItem.classList.add('subcategory-level3');
            
            if (currentCategory === category.id) {
                categoryItem.classList.add('active-category');
            }
            
            categoryItem.innerHTML = `
                ${category.name} 
                <span class="category-count">(${courseCount})</span>
            `;
            
            categoryItem.addEventListener('click', () => {
                filterByCategory(category.id, category.name);
            });
            
            container.appendChild(categoryItem);
            
            // Render children
            if (category.children && category.children.length > 0) {
                category.children.forEach(child => {
                    renderCategoryItem(child, container, level + 1);
                });
            }
        }

        // Filter courses by category
        function filterByCategory(categoryId, categoryName) {
            currentCategory = categoryId;
            
            // Update page title if category is selected
            const pageTitle = document.getElementById('page-title');
            if (categoryId === null) {
                pageTitle.textContent = 'Course catalog';
            } else {
                pageTitle.textContent = categoryName;
            }
            
            // Re-render categories to update active state
            renderCategories();
            
            // Re-render courses with filter
            renderCourses();
        }

        // Get category by ID
        function getCategoryById(id) {
            return categories.find(category => category.id === id);
        }
        
        // Get parent category chain
        function getCategoryChain(categoryId) {
            const result = [];
            let current = getCategoryById(categoryId);
            
            while (current) {
                result.unshift(current);
                current = current.parent_id ? getCategoryById(current.parent_id) : null;
            }
            
            return result;
        }

        // Render courses based on current filter
        function renderCourses() {
            const courseContainer = document.getElementById('course-container');
            courseContainer.innerHTML = '';
            
            let filteredCourses = courses;
            console.log('Filtered courses:', filteredCourses);
            console.log('currentCategory:', currentCategory);
            // Apply category filter
            if (currentCategory) {
                const childCategories = [currentCategory, ...getChildCategories(currentCategory)];
                filteredCourses = courses.filter(course => 
                    childCategories.includes(course.category_id)
                );
            }
            
            // Render each course
            filteredCourses.forEach(course => {
                const categoryInfo = getCategoryById(course.category_id);
                const categoryName = categoryInfo ? categoryInfo.name : '';
                
                // Find the top-level category
                const categoryChain = getCategoryChain(course.category_id);
                const topCategory = categoryChain.length > 0 ? categoryChain[0].name : '';
                
                const courseCard = document.createElement('div');
                courseCard.className = 'col-md-6 col-lg-4 mb-4';
                courseCard.innerHTML = `
                    <div class="course-card">
                        <div class="course-img-container">
                            <img src="${course.preview}" alt="${course.name}" class="course-img">
                            <div class="course-category-badge">${topCategory}</div>
                        </div>
                        <div class="course-content">
                            <h3 class="course-title">${course.name}</h3>
                            <p class="course-description">${course.description}</p>
                        </div>
                    </div>
                `;
                courseContainer.appendChild(courseCard);
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', async () => {
            await fetchCategories();
            await fetchCourses();
        });
    </script>
</body>
</html>