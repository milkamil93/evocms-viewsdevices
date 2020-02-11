# Views Devices
Разделяет папку views на подпадки views/mobile,views/desktop,views/tablet

##Установка

##Использование
Если нету папки tablet, то шаблоны чекаются из папки mobile, в свою очередь, если нету mobile, то смотрится папка desktop
Аналогично с контроллерами
```
php artisan package:installrequire "milkamil93/evocms-viewsdevices" "*"
php artisan vdt:install
```

Сделан на основе https://github.com/Ser1ous/seriousCustomTemplate
