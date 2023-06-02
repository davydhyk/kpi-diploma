<?php

require_once __DIR__ . '/load.php';

//dbg($_SERVER['REQUEST_URI']);
//dbg(parse_url($_SERVER['REQUEST_URI']));

Route::get('/aggregate', [\Controllers\IntegrationController::class, 'aggregate']);

Route::get('/worldRegion/:id', [\Controllers\WorldRegionController::class, 'getById']);
Route::get('/worldRegions', [\Controllers\WorldRegionController::class, 'getAll']);

Route::get('/country/:id', [\Controllers\CountryController::class, 'getById']);
Route::get('/countries', [\Controllers\CountryController::class, 'getByFilter']);

Route::get('/sailingArea/:id', [\Controllers\SailingAreaController::class, 'getById']);
Route::get('/sailingAreas', [\Controllers\SailingAreaController::class, 'getAll']);

Route::get('/base/:id', [\Controllers\BaseController::class, 'getById']);
Route::get('/bases', [\Controllers\BaseController::class, 'getByFilter']);

Route::get('/equipment/:id', [\Controllers\EquipmentController::class, 'getById']);
Route::get('/equipment', [\Controllers\EquipmentController::class, 'getAll']);

Route::get('/shipyard/:id', [\Controllers\ShipyardController::class, 'getById']);
Route::get('/shipyards', [\Controllers\ShipyardController::class, 'getAll']);

Route::get('/boat/:id', [\Controllers\BoatController::class, 'getById']);
Route::get('/boats', [\Controllers\BoatController::class, 'getByFilter']);

$router = new Router(Route::$routes);
$router->process($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

//boat-info-list
//amenities