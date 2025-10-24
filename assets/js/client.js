// Client-side JavaScript for upload page

const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1920;
const QUALITY = 0.85;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    
    // Don't initialize upload if order is in printing status
    if (ORDER_STATUS === 'printing') {
        return;
    }
    
    if (!uploadArea || !fileInput) {
        return;
    }
    
    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });
});

// Handle file uploads
async function handleFiles(files) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot upload photos while order is in printing status.');
        return;
    }
    
    const uploadArea = document.getElementById('uploadArea');
    const progressContainer = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    progressContainer.style.display = 'block';
    uploadArea.style.display = 'none';
    
    const filesArray = Array.from(files);
    const totalFiles = filesArray.length;
    let uploaded = 0;
    
    for (let i = 0; i < filesArray.length; i++) {
        const file = filesArray[i];
        
        try {
            // Resize image before upload
            const resizedFile = await resizeImage(file);
            
            // Upload file
            await uploadFile(resizedFile);
            
            uploaded++;
            const progress = (uploaded / totalFiles) * 100;
            progressFill.style.width = progress + '%';
            progressText.textContent = `Uploading ${uploaded} of ${totalFiles} photos...`;
            
        } catch (error) {
            console.error('Upload error:', error);
            alert(`Failed to upload ${file.name}: ${error.message}`);
        }
    }
    
    // Reset UI
    setTimeout(() => {
        progressContainer.style.display = 'none';
        uploadArea.style.display = 'block';
        progressFill.style.width = '0%';
        progressText.textContent = 'Uploading...';
        
        // Reload page to show new photos
        location.reload();
    }, 1000);
}

// Resize image client-side
function resizeImage(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            const img = new Image();
            
            img.onload = () => {
                let width = img.width;
                let height = img.height;
                
                // Calculate new dimensions
                if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                    if (width > height) {
                        height = (height / width) * MAX_WIDTH;
                        width = MAX_WIDTH;
                    } else {
                        width = (width / height) * MAX_HEIGHT;
                        height = MAX_HEIGHT;
                    }
                }
                
                // Create canvas
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                
                // Draw and compress
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob((blob) => {
                    const resizedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(resizedFile);
                }, 'image/jpeg', QUALITY);
            };
            
            img.onerror = reject;
            img.src = e.target.result;
        };
        
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

// Upload file to server
function uploadFile(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        
        const xhr = new XMLHttpRequest();
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    resolve(response);
                } else {
                    reject(new Error(response.error || 'Upload failed'));
                }
            } else {
                reject(new Error('Upload failed'));
            }
        };
        
        xhr.onerror = () => reject(new Error('Network error'));
        
        xhr.open('POST', `api_upload.php?code=${UNIQUE_CODE}`);
        xhr.send(formData);
    });
}

// Update photo quantity
function updateQuantity(photoId, quantity) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot modify quantities while order is in printing status.');
        return;
    }
    
    const formData = new FormData();
    formData.append('quantity', quantity);
    
    fetch(`api_photo.php?action=update_quantity&id=${photoId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSummary(data.order);
        } else {
            alert('Failed to update quantity: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update quantity');
    });
}

// Delete photo
function deletePhoto(photoId) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot delete photos while order is in printing status.');
        return;
    }
    
    if (!confirm('Are you sure you want to delete this photo?')) {
        return;
    }
    
    fetch(`api_photo.php?action=delete&id=${photoId}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove photo from DOM
            const photoCard = document.querySelector(`[data-photo-id="${photoId}"]`);
            if (photoCard) {
                photoCard.remove();
            }
            
            // Update summary
            updateSummary(data.order);
            
            // Update photo count
            const photoCards = document.querySelectorAll('.photo-card');
            document.getElementById('totalPhotos').textContent = `(${photoCards.length})`;
        } else {
            alert('Failed to delete photo: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete photo');
    });
}

// Update order summary
function updateSummary(order) {
    document.getElementById('summaryPhotos').textContent = order.total_photos;
    document.getElementById('summaryTotal').textContent = formatPrice(order.total_cost);
    
    // Reload page to get updated order data
    setTimeout(() => {
        location.reload();
    }, 500);
}

// Add product to order
function addProduct(productId) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot add products while order is in printing status.');
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch(`api_products.php?action=add_product&code=${UNIQUE_CODE}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to order!');
            location.reload();
        } else {
            alert('Failed to add product: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add product');
    });
}

// Remove product from order
function removeProduct(orderProductId) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot remove products while order is in printing status.');
        return;
    }
    
    if (!confirm('Are you sure you want to remove this product from your order?')) {
        return;
    }
    
    fetch(`api_products.php?action=remove_product&code=${UNIQUE_CODE}&order_product_id=${orderProductId}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product removed from order!');
            location.reload();
        } else {
            alert('Failed to remove product: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove product');
    });
}

// Update photo option
function updatePhotoOption(photoId, option, checked) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot modify options while order is in printing status.');
        location.reload();
        return;
    }
    
    fetch(`api_photo_options.php?action=update_option&id=${photoId}&option=${option}&value=${checked}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload to update totals
            location.reload();
        } else {
            alert('Failed to update option: ' + data.error);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update option');
        location.reload();
    });
}

// Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

