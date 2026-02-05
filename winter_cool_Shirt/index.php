<?php include 'includes/config.php'; ?>
<?php include 'includes/header.php'; ?>

<style>
/* CSS ส่วน Slideshow และ Card */
.slideshow-container { width: 95%; max-width: 1000px; height: 400px; position: relative; margin: 20px auto 30px auto; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
@media (max-width: 768px) { .slideshow-container { height: 250px; border-radius: 15px; } }
.mySlides { display: none; width: 100%; height: 100%; }
.mySlides img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
.numbertext { color: #fff; font-size: 13px; padding: 8px 15px; position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.3); border-radius: 20px; backdrop-filter: blur(4px); z-index: 10; }
.text { color: #fff; font-size: 20px; font-weight: 500; padding: 20px; position: absolute; bottom: 0; width: 100%; text-align: center; background: linear-gradient(transparent, rgba(0,0,0,0.6)); }
.dot-container { text-align: center; margin-top: -45px; position: relative; z-index: 20; }
.dot { height: 10px; width: 10px; margin: 0 4px; background-color: rgba(255,255,255,0.6); border-radius: 50%; display: inline-block; transition: all 0.3s ease; cursor: pointer; }
.active { background-color: #ffffff; width: 25px; border-radius: 10px; }
.fade { animation-name: fade; animation-duration: 1s; }
@keyframes fade { from {opacity: 0.7} to {opacity: 1} }

.card-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 25px; padding: 0 20px 60px 20px; }
.card { background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 18px; text-align: center; width: 260px; transition: 0.3s; }
.card:hover { transform: translateY(-8px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.card img { width: 100%; height: 240px; object-fit: cover; border-radius: 14px; margin-bottom: 15px; }
.card h4 { margin: 10px 0; font-size: 18px; color: #333; }
.card .price { color: #2e86c1; font-weight: bold; font-size: 22px; margin-bottom: 15px; }
.btn-detail { background: #2e86c1; color: white; border: none; padding: 12px; border-radius: 12px; cursor: pointer; font-weight: 600; width: 100%; transition: 0.2s; text-decoration: none; display: block; }
.btn-detail:hover { background: #21618c; }
</style>

<div style="text-align:center; margin: 40px 0 10px 0; font-family: 'Kanit', sans-serif;">
    <h2 style="color:#2e86c1; font-size: 32px; margin-bottom: 5px;">Winter Cool Shirt</h2>
    <p style="font-size:16px; color:#888;">เสื้อกันหนาวพรีเมียม อบอุ่น ใส่สบายในทุกโอกาส</p>
</div>

<div class="slideshow-container">
    <div class="mySlides fade">
        <div class="numbertext">1 / 2</div>
        <img src="promotion/โปรโมชั่น1.png">
        <div class="text">Hoodies Series : สไตล์ที่มาพร้อมความอบอุ่น</div>
    </div>
    <div class="mySlides fade">
        <div class="numbertext">2 / 2</div>
        <img src="promotion/โปรโมชั่น2.png">
        <div class="text">Sweaters Collection : สัมผัสนุ่ม ใส่สบายตลอดวัน</div>
    </div>
</div>

<div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
</div>

<h3 style="color:#2e86c1; text-align:center; margin:50px 0 30px; font-size:24px; font-family: 'Kanit', sans-serif;">
    ⭐ สินค้าแนะนำสำหรับคุณ
</h3>

<div class="card-container" style="font-family: 'Kanit', sans-serif;">
    <div class="card">
        <img src="uploads/เสื้อฮู้ดสีดำ.jpg">
        <h4>เสื้อฮู้ดสีดำ</h4>
        <div class="price">160 บาท</div>
        <a href="product_detail.php?product_id=11" class="btn-detail">ดูรายละเอียด</a>
    </div>

    <div class="card">
        <img src="uploads/เสื้อสเวตเตอร์สีชมพู.jpg">
        <h4>เสื้อสเวตเตอร์สีชมพู</h4>
        <div class="price">220 บาท</div>
        <a href="product_detail.php?product_id=9" class="btn-detail">ดูรายละเอียด</a>
    </div>

    <div class="card">
        <img src="uploads/แจ็คเก็ตยีนส์สีฟ้าอ่อน.jpg">
        <h4>แจ็คเก็ตยีนส์สีฟ้าอ่อน</h4>
        <div class="price">549 บาท</div>
        <a href="product_detail.php?product_id=15" class="btn-detail">ดูรายละเอียด</a>
    </div>
</div>

<script>
let slideIndex = 0;
let slideTimer;
showSlides();
function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    for (i = 0; i < slides.length; i++) { slides[i].style.display = "none"; }
    slideIndex++;
    if (slideIndex > slides.length) {slideIndex = 1}    
    for (i = 0; i < dots.length; i++) { dots[i].className = dots[i].className.replace(" active", ""); }
    if(slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";  
    if(dots[slideIndex-1]) dots[slideIndex-1].className += " active";
    clearTimeout(slideTimer);
    slideTimer = setTimeout(showSlides, 5000); 
}
function currentSlide(n) { slideIndex = n - 1; showSlides(); }
</script>

<?php include 'includes/footer.php'; ?>