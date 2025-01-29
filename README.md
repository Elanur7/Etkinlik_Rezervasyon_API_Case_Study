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
```
```bash
cd Etkinlik_Rezervasyon_API_Case_Study
```

### 2. Bağımlılıkları Yükleyin
Composer ile PHP bağımlılıklarını yükleyin:
```bash
composer install
```

### 3. JWT Paketini Yükleyin
JWT kimlik doğrulama için tymon/jwt-auth paketini yükleyin:
```bash
composer require tymon/jwt-auth
```

### 4. .env Dosyasını Oluşturun
.env.example dosyasını .env olarak kopyalayın ve dosyadaki veritabanı ve JWT ayarlarını kendi ortamınıza göre düzenleyin:
```bash
cp .env.example .env
```

Örnek Veritabanı Ayarları:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=veritabani_adi
DB_USERNAME=kullanici_adi
DB_PASSWORD=sifre
```

JWT Ayarları:
.env dosyasına şu satırı ekleyin veya düzenleyin (JWT Secret):
```bash
JWT_SECRET=your_jwt_secret_key
```

### 5. JWT Secret Anahtarını Oluşturun
JWT'yi kullanabilmek için JWT secret anahtarını oluşturmanız gerekir. Aşağıdaki komutla bu anahtarı oluşturabilirsiniz:
```bash
php artisan jwt:secret
```

### 6. Veritabanı Migrations'larını Çalıştırın
Veritabanı tablolarını oluşturmak için migrations'ları çalıştırın:
```bash
php artisan migrate
```

### 7. DB Seed
Otomatik veri tanımlamak için seeder çalıştırın:
```bash
php artisan db:seed
```

### 8. Uygulama Anahtarını Ayarlayın
Laravel için bir uygulama anahtarı ayarlayın:
```bash
php artisan key:generate
```

### 9. Çalıştırma
Projeyi çalıştırmak için aşağıdaki komutu kullanabilirsiniz:
```bash
php artisan serve
```
