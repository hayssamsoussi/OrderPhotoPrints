// Client-side JavaScript for upload page

const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1920;
const QUALITY = 0.85;

// Language support
const translations = {
    en: {
        title: "Photo Order Portal",
        welcome: "Welcome, ",
        announcement: "100 Photos for 20$ ends on 11/31 | Sales",
        orderStatus: "Order Status",
        statusReceived: "Received",
        statusPrinting: "Printing",
        statusShipping: "Shipping",
        statusDone: "Done",
        uploadPhotos: "Upload Photos",
        uploadDisabled: "📸 Order is currently in printing status. Uploads are disabled.",
        uploadPlaceholder: "Click or drag photos here to upload",
        uploadHint: "Supports JPG, PNG, GIF (max 10MB each)",
        uploading: "Uploading...",
        yourPhotos: "Your Photos",
        clickForOptions: "👆 Click for options",
        photoOptions: "Photo Options",
        frame: "Frame",
        frameDescription: "Add a professional frame to protect and enhance your photo. Available in black, white, or wood finish.",
        woodboard: "Woodboard",
        woodboardDescription: "Print your photo on a beautiful wooden board for a rustic, artistic look. Perfect for rustic or modern decor.",
        biggerSize: "Bigger Size",
        biggerSizeDescription: "Upgrade to a larger print size. Perfect for making your favorite photos stand out as wall art.",
        close: "Close",
        quantity: "Quantity:",
        options: "Options:",
        addProducts: "Add Products to Order",
        addToOrder: "Add to Order",
        productsInOrder: "Products in Order",
        qty: "Qty",
        remove: "Remove",
        orderSummary: "Order Summary",
        totalPhotos: "Total Photos:",
        pricePerPhoto: "Price per Photo:",
        products: "Products:",
        totalCost: "Total Cost:",
        total: "Total:",
        addToCart: "Add To Cart"
    },
    ar: {
        title: "بوابة طلب الصور",
        welcome: "مرحباً، ",
        announcement: "100 صورة مقابل 20 دولار تنتهي في 31/11 | عروض",
        orderStatus: "حالة الطلب",
        statusReceived: "مستلم",
        statusPrinting: "قيد الطباعة",
        statusShipping: "قيد الشحن",
        statusDone: "منجز",
        uploadPhotos: "رفع الصور",
        uploadDisabled: "📸 الطلب حاليًا في حالة الطباعة. رفع الصور معطل.",
        uploadPlaceholder: "انقر أو اسحب الصور هنا للرفع",
        uploadHint: "يدعم JPG, PNG, GIF (حد أقصى 10 ميجابايت لكل صورة)",
        uploading: "جاري الرفع...",
        yourPhotos: "صورك",
        clickForOptions: "👆 انقر للخيارات",
        photoOptions: "خيارات الصورة",
        frame: "إطار",
        frameDescription: "أضف إطارًا احترافيًا لحماية وتحسين صورتك. متوفر باللون الأسود أو الأبيض أو الخشبي.",
        woodboard: "لوح خشبي",
        woodboardDescription: "اطبع صورتك على لوح خشبي جميل للحصول على مظهر ريفي فني. مثالي للديكور الريفي أو العصري.",
        biggerSize: "حجم أكبر",
        biggerSizeDescription: "قم بالترقية إلى حجم طباعة أكبر. مثالي لجعل صورك المفضلة تبرز كفن على الحائط.",
        close: "إغلاق",
        quantity: "الكمية:",
        options: "الخيارات:",
        addProducts: "إضافة منتجات للطلب",
        addToOrder: "إضافة للطلب",
        productsInOrder: "المنتجات في الطلب",
        qty: "الكمية",
        remove: "إزالة",
        orderSummary: "ملخص الطلب",
        totalPhotos: "إجمالي الصور:",
        pricePerPhoto: "سعر الصورة الواحدة:",
        products: "المنتجات:",
        totalCost: "التكلفة الإجمالية:",
        total: "الإجمالي:",
        addToCart: "إضافة إلى السلة"
    }
};

let currentLang = localStorage.getItem('language') || 'en';

// Function to get translation
function t(key) {
    return translations[currentLang][key] || translations.en[key] || key;
}

// Function to translate all elements
function translatePage() {
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        const translation = t(key);
        
        // Handle special cases for welcome message
        if (key === 'welcome') {
            // Extract client name from original content
            const originalText = element.getAttribute('data-original-text') || element.textContent;
            let clientName = '';
            
            if (originalText.includes('Welcome, ')) {
                clientName = originalText.split('Welcome, ')[1];
            } else if (originalText.includes('مرحباً، ')) {
                clientName = originalText.split('مرحباً، ')[1];
            } else {
                clientName = originalText.replace(/Welcome, |مرحباً، /gi, '');
            }
            
            // Store original text for future translations
            if (!element.getAttribute('data-original-text')) {
                element.setAttribute('data-original-text', originalText);
            }
            
            element.textContent = translation + clientName;
        } else {
            element.textContent = translation;
        }
    });
    
    // Update lang switcher
    document.getElementById('currentLang').textContent = currentLang.toUpperCase();
    
    // Update document direction
    document.documentElement.dir = currentLang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = currentLang;
    
    // Update photo hover texts
    setInitialHoverTexts();
}

// Toggle language
function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'ar' : 'en';
    localStorage.setItem('language', currentLang);
    translatePage();
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
    // Translate page
    translatePage();
    
    // Initialize upload functionality
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
    
    // Update footer total
    const footerTotal = document.getElementById('footerTotal');
    if (footerTotal) {
        footerTotal.textContent = formatPrice(order.total_cost);
    }
    
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

// Submit order
function submitOrder() {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        alert('Cannot submit order while it is in printing status.');
        return;
    }
    
    if (!confirm('Are you sure you want to submit this order for processing?')) {
        return;
    }
    
    // In a real application, this would send the order to processing
    // For now, we'll just show a success message
    alert('Order submitted successfully! Your photos are being processed.');
    
    // Optionally reload the page to show updated status
    // location.reload();
}

// Current photo ID being edited
let currentPhotoId = null;

// Toggle photo options modal
function togglePhotoOptions(photoId) {
    // Check if order is in printing status
    if (ORDER_STATUS === 'printing') {
        return;
    }
    
    currentPhotoId = photoId;
    
    // Get photo data
    const photoCard = document.querySelector(`[data-photo-id="${photoId}"]`);
    if (!photoCard) return;
    
    const photoImg = photoCard.querySelector('img');
    const photoPath = photoImg ? photoImg.src : '';
    
    // Set modal photo preview
    document.getElementById('modalPhotoPreview').src = photoPath;
    
    // Load current options
    loadPhotoOptions(photoId);
    
    // Show modal
    document.getElementById('photoOptionsModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Load photo options from database
function loadPhotoOptions(photoId) {
    fetch(`api_photo.php?action=get_options&id=${photoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('checkFrame').checked = data.options.has_frame || false;
                document.getElementById('checkWoodboard').checked = data.options.has_woodboard || false;
                document.getElementById('checkBiggerSize').checked = data.options.bigger_size || false;
            }
        })
        .catch(error => {
            console.error('Error loading options:', error);
        });
}

// Toggle option checkbox
function toggleOption(option, checked) {
    if (!currentPhotoId) return;
    
    const optionMap = {
        'frame': 'has_frame',
        'woodboard': 'has_woodboard',
        'bigger_size': 'bigger_size'
    };
    
    const dbOption = optionMap[option];
    if (!dbOption) return;
    
    fetch(`api_photo_options.php?action=update_option&id=${currentPhotoId}&option=${dbOption}&value=${checked}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update visual feedback
            const optionCard = document.getElementById('option' + option.charAt(0).toUpperCase() + option.slice(1));
            if (optionCard) {
                if (checked) {
                    optionCard.classList.add('selected');
                } else {
                    optionCard.classList.remove('selected');
                }
            }
            // Reload page to update totals
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(error => {
        console.error('Error updating option:', error);
    });
}

// Close photo options modal
function closePhotoOptionsModal() {
    document.getElementById('photoOptionsModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentPhotoId = null;
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('photoOptionsModal');
    if (event.target === modal) {
        closePhotoOptionsModal();
    }
});

// Set initial hover text for all photos
function setInitialHoverTexts() {
    document.querySelectorAll('.photo-image').forEach(img => {
        if (!img.getAttribute('data-hover-text')) {
            img.setAttribute('data-hover-text', t('clickForOptions'));
        }
    });
}

