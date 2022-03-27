### PR00B251 Produkto vystymo projekto modulio projektas

Tema: „Kelių stebėsenos programinė įranga“  
Komandos nr.: K042  
Vadovas: Andrius Kriščiūnas

### API

---

`POST /api/v1/submit/latlng`

Headers:
- api_key: string
- lat: float 
- lng: float

---

`GET /api/v1/get/location/{id}`

- id: The id of the location to get

**Returns:**  
Status `200` if location was found

- id: The id of the location
- lat: The latitude of the location
- lng: The longitude of the location
- created_at: The time the location was created

Status: `404` if location not found  
`{"error":"Location not found"}`

--- 

`GET /api/v1/getimage/{id}.png`  

- id: The id of the image to get

**Returns:**  
Status `200` if location was found

- Content-Type: image/png

Status: `404` if location not found  
`{"error":"Image not found"}`

---

`GET /api/v1/location/all`

**Returns:**  
JSON array of all locations

`/api/v1/test`

**Returns:**  
Status `200` if API is working

---

### Frameworks

- [Symfony 6.0.4](https://symfony.com/releases/6.0)
- [PHP 8.0](https://secure.php.net/downloads.php)
- [Leaflet](https://leafletjs.com/about/license)
  - [Geocoder](https://github.com/perliedman/leaflet-control-geocoder)
  - [Esri](https://developers.arcgis.com/esri-leaflet/)
- [Bootstrap 5.0](https://getbootstrap.com/docs/5.0/getting-started/introduction/)
- [PostgreSQL](https://www.postgresql.org/download/)
- [Postgis](https://postgis.net/)
- [pgAdmin4](https://www.pgadmin.org/)