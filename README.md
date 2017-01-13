# atlas-geolokacja
poniższa aplikacja integruje dane demograficzne (miasto, populacja) z danymi geolokacyjnymi
(szerość i długość geograficzna).
 Dane demograficzne znajdują się w bazie mysql w przykładowej tabeli citiespoland
kulumny: miasto (text), populacja (int).
 Dane geolokalizacyjne pochodzą z Google Maps Geocoding API i są odpowiednio umieszczane
w kolumnach lat(decimal(10,8)), lng(decimal (11,8)). 
 Przebieg wykonywania kodu jest spowolniony przez komendę usleep (100000); ze względu
limit sekundowy zapytań do Google Maps Geocoding API.
