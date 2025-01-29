# Etkinlik Rezervasyon API Case Study

Bu proje, Laravel framework'ü kullanılarak geliştirilmiş bir REST API sistemidir. API, etkinlik rezervasyonu yönetimi için JWT tabanlı kimlik doğrulama kullanmaktadır.

## Gereksinimler

Projenin çalışabilmesi için aşağıdaki yazılımlar gereklidir:
- PHP >= 8+
- Composer
- MySQL
- Laravel
- JWT (JSON Web Token) için `tymon/jwt-auth` paketi

## Kurulum

### 1. Depoyu Klonlayın

İlk olarak, projenin repository'sini klonlayın:

```bash
git clone https://github.com/Elanur7/Etkinlik_Rezervasyon_API_Case_Study.git 
cd proje-adi
```

### 2. Bağımlılıkları Yükleyin
Composer ile PHP bağımlılıklarını yükleyin:
composer install

### 3. JWT Paketini Yükleyin
JWT kimlik doğrulama için tymon/jwt-auth paketini yükleyin:
composer require tymon/jwt-auth

### 4. .env Dosyasını Oluşturun
.env.example dosyasını .env olarak kopyalayın ve dosyadaki veritabanı ve JWT ayarlarını kendi ortamınıza göre düzenleyin:
cp .env.example .env

Örnek Veritabanı Ayarları:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=veritabani_adi
DB_USERNAME=kullanici_adi
DB_PASSWORD=sifre

JWT Ayarları:
.env dosyasına şu satırı ekleyin veya düzenleyin (JWT Secret):
JWT_SECRET=your_jwt_secret_key

### 5. JWT Secret Anahtarını Oluşturun
JWT'yi kullanabilmek için JWT secret anahtarını oluşturmanız gerekir. Aşağıdaki komutla bu anahtarı oluşturabilirsiniz:
php artisan jwt:secret

### 6. Veritabanı Migrations'larını Çalıştırın
Veritabanı tablolarını oluşturmak için migrations'ları çalıştırın:
php artisan migrate

### 7. DB Seed
Otomatik veri tanımlamak için seeder çalıştırın:
php artisan db:seed

### 8. Uygulama Anahtarını Ayarlayın
Laravel için bir uygulama anahtarı ayarlayın:
php artisan key:generate

### 9. Çalıştırma
Projeyi çalıştırmak için aşağıdaki komutu kullanabilirsiniz:
php artisan serve
