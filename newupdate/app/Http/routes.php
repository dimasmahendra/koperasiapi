<?php

Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');


Route::get('request/newpassword/{email}/{token}', 'ResetpasswordController@index');

Route::post('request/newpassword', 'ResetpasswordController@resetpass');



Route::controllers([
    'password' => 'Auth\PasswordController',
]);



/* -------------------- API -------------------------- */
Route::post('api/auth','ApiController@auth');

Route::post('api/logout','ApiController@logout');

Route::post('api/getprofile','ApiController@getprofile');

Route::post('api/updateprofile','ApiController@updateprofile');

Route::post('api/updateimage','ApiController@updateimage');

Route::post('api/getprovinsi','ApiController@getprovinsi');

Route::post('api/getkabupatenkota','ApiController@getkabupatenkota');

Route::post('api/getkecamatan','ApiController@getkecamatan');

Route::post('api/getkelurahan','ApiController@getkelurahan');

Route::post('api/getalamat','ApiController@getalamat');

Route::post('api/getinfokementerian','ApiController@getinfokementerian');

Route::post('api/getinfokoperasi','ApiController@getinfokoperasi');

Route::post('api/forgotpassword','ApiController@forgotpassword');

Route::post('api/resetpassword','ApiController@resetpassword');

Route::post('api/gettrainingkoperasi','ApiController@gettrainingkoperasi');

Route::post('api/gettrainingkementerian','ApiController@gettrainingkementerian');

Route::post('api/getseminarkementerian','ApiController@getseminarkementerian');

Route::post('api/getseminarkoperasi','ApiController@getseminarkoperasi');

Route::post('api/getrat','ApiController@getrat');


Route::post('api/insertkehadiranrat','ApiController@insertkehadiranrat');

Route::post('api/insertbookingtrainingkoperasi','ApiController@insertbookingtrainingkoperasi');

Route::post('api/insertbookingseminarkoperasi','ApiController@insertbookingseminarkoperasi');

Route::post('api/insertbookingtrainingkementerian','ApiController@insertbookingtrainingkementerian');

Route::post('api/insertbookingseminarkementerian','ApiController@insertbookingseminarkementerian');


Route::post('api/getinfokoperasidetail','ApiController@getinfokoperasidetail');

Route::post('api/insertkomentarinfokoperasi','ApiController@insertkomentarinfokoperasi');



Route::post('api/getinfokementeriandetail','ApiController@getinfokementeriandetail');

Route::post('api/insertkomentarinfokementerian','ApiController@insertkomentarinfokementerian');


Route::post('api/tes','ApiController@tes');

Route::post('api/getshu','ApiController@getshu');


Route::post('api/getmyproduk','ApiController@getmyproduk');

Route::post('api/getkategori','ApiController@getkategori');

Route::post('api/inserttransaksi','ApiController@inserttransaksi');



Route::post('api/getmydetailtemp','ApiController@getmydetailtemp');

Route::post('api/insertdetailtemp','ApiController@insertdetailtemp');

Route::post('api/editdetailtemp','ApiController@editdetailtemp');

Route::post('api/updatedetailtemp','ApiController@updatedetailtemp');

Route::post('api/deletedetailtemp','ApiController@deletedetailtemp');


Route::post('api/checkout','ApiController@checkout');

Route::post('api/canceltrans','ApiController@canceltrans');

Route::post('api/cektrans','ApiController@cektrans');


Route::post('api/getmytransaksi','ApiController@getmytransaksi');

Route::post('api/getmytransaksidetail','ApiController@getmytransaksidetail');









/* -------------------- APIv2 (api utk web admin) -------------------------- */

Route::post('apiv2/loginadmin','Apiv2Controller@loginadmin');

Route::post('apiv2/logout','Apiv2Controller@logout');

Route::post('apiv2/getprofile','Apiv2Controller@getprofile');

Route::post('apiv2/updateprofile','Apiv2Controller@updateprofile');


//koperasi

Route::post('apiv2/getmykoperasi','Apiv2Controller@getmykoperasi');
Route::post('apiv2/updatemykoperasi','Apiv2Controller@updatemykoperasi');


// Admin Koperasi
Route::post('apiv2/getadminkoperasi','Apiv2Controller@getadminkoperasi');

Route::post('apiv2/insertadminkoperasi','Apiv2Controller@insertadminkoperasi');

Route::post('apiv2/editadminkoperasi','Apiv2Controller@editadminkoperasi');

Route::post('apiv2/updateadminkoperasi','Apiv2Controller@updateadminkoperasi');

Route::post('apiv2/deleteadminkoperasi','Apiv2Controller@deleteadminkoperasi');

Route::post('apiv2/getakseskoperasi','Apiv2Controller@getakseskoperasi');




// Alamat------------------------------------------------------------------//

Route::get('apiv2/getprovinsi','Apiv2Controller@getprovinsi');

Route::post('apiv2/getkabupatenkota','Apiv2Controller@getkabupatenkota');

Route::post('apiv2/getkecamatan','Apiv2Controller@getkecamatan');

Route::post('apiv2/getkelurahan','Apiv2Controller@getkelurahan');



//Anggota koperasi

Route::post('apiv2/getanggotakoperasi','Apiv2Controller@getanggotakoperasi');

Route::post('apiv2/insertanggotakoperasi','Apiv2Controller@insertanggotakoperasi');

Route::post('apiv2/updateanggotakoperasi','Apiv2Controller@updateanggotakoperasi');

Route::post('apiv2/deleteanggotakoperasi','Apiv2Controller@deleteanggotakoperasi');

Route::post('apiv2/getanggotakoperasidetail','Apiv2Controller@getanggotakoperasidetail');


//info koperasi

Route::post('apiv2/getinfokoperasi','Apiv2Controller@getinfokoperasi');

Route::post('apiv2/insertinfokoperasi','Apiv2Controller@insertinfokoperasi');

Route::post('apiv2/editinfokoperasi','Apiv2Controller@editinfokoperasi');

Route::post('apiv2/updateinfokoperasi','Apiv2Controller@updateinfokoperasi');

Route::post('apiv2/deleteinfokoperasi','Apiv2Controller@deleteinfokoperasi');


//Training Koperasi

Route::post('apiv2/gettrainingkoperasi','Apiv2Controller@gettrainingkoperasi');

Route::post('apiv2/inserttrainingkoperasi','Apiv2Controller@inserttrainingkoperasi');

Route::post('apiv2/edittrainingkoperasi','Apiv2Controller@edittrainingkoperasi');

Route::post('apiv2/updatetrainingkoperasi','Apiv2Controller@updatetrainingkoperasi');

Route::post('apiv2/deletetrainingkoperasi','Apiv2Controller@deletetrainingkoperasi');


//Booking

Route::post('apiv2/getbookingtrainingkoperasi','Apiv2Controller@getbookingtrainingkoperasi');

Route::post('apiv2/getbookingtrainingkoperasiwhere','Apiv2Controller@getbookingtrainingkoperasiwhere');

Route::post('apiv2/getbookingseminarkoperasi','Apiv2Controller@getbookingseminarkoperasi');

Route::post('apiv2/getbookingseminarkoperasiwhere','Apiv2Controller@getbookingseminarkoperasiwhere');


//Seminar Koperasi

Route::post('apiv2/getseminarkoperasi','Apiv2Controller@getseminarkoperasi');

Route::post('apiv2/insertseminarkoperasi','Apiv2Controller@insertseminarkoperasi');

Route::post('apiv2/editseminarkoperasi','Apiv2Controller@editseminarkoperasi');

Route::post('apiv2/updateseminarkoperasi','Apiv2Controller@updateseminarkoperasi');

Route::post('apiv2/deleteseminarkoperasi','Apiv2Controller@deleteseminarkoperasi');



//Tahun operasi

Route::post('apiv2/gettahunoperasi','Apiv2Controller@gettahunoperasi');

Route::post('apiv2/inserttahunoperasi','Apiv2Controller@inserttahunoperasi');

Route::post('apiv2/edittahunoperasi','Apiv2Controller@edittahunoperasi');

Route::post('apiv2/updatetahunoperasi','Apiv2Controller@updatetahunoperasi');


Route::post('apiv2/updatestatustahunoperasi','Apiv2Controller@updatestatustahunoperasi');

Route::post('apiv2/deletetahunoperasi','Apiv2Controller@deletetahunoperasi');




//Biaya Usaha

Route::post('apiv2/getbiayausaha','Apiv2Controller@getbiayausaha');

Route::post('apiv2/insertbiayausaha','Apiv2Controller@insertbiayausaha');

Route::post('apiv2/editbiayausaha','Apiv2Controller@editbiayausaha');

Route::post('apiv2/updatebiayausaha','Apiv2Controller@updatebiayausaha');

Route::post('apiv2/deletebiayausaha','Apiv2Controller@deletebiayausaha');


//Jurnalkoperasi

Route::post('apiv2/getjurnalaktif','Apiv2Controller@getjurnalaktif');

Route::post('apiv2/getjurnalwhere','Apiv2Controller@getjurnalwhere');

Route::post('apiv2/resetjurnal','Apiv2Controller@resetjurnal');

Route::post('apiv2/kalkulasijurnal','Apiv2Controller@kalkulasijurnal');


//Simpanan

Route::post('apiv2/getidnamaanggota','Apiv2Controller@getidnamaanggota');

Route::post('apiv2/getsimpanan','Apiv2Controller@getsimpanan');

Route::post('apiv2/cekjenissimpanan','Apiv2Controller@cekjenissimpanan');

Route::post('apiv2/insertsimpanan','Apiv2Controller@insertsimpanan');

Route::post('apiv2/editsimpanan','Apiv2Controller@editsimpanan');

Route::post('apiv2/updatesimpanan','Apiv2Controller@updatesimpanan');

Route::post('apiv2/deletesimpanan','Apiv2Controller@deletesimpanan');


//Komponen SHU

Route::post('apiv2/gettipekomponenshu','Apiv2Controller@gettipekomponenshu');

Route::post('apiv2/getkomponenshu','Apiv2Controller@getkomponenshu');

Route::post('apiv2/insertkomponenshu','Apiv2Controller@insertkomponenshu');

Route::post('apiv2/editkomponenshu','Apiv2Controller@editkomponenshu');

Route::post('apiv2/updatekomponenshu','Apiv2Controller@updatekomponenshu');

Route::post('apiv2/deletekomponenshu','Apiv2Controller@deletekomponenshu');


////SHU

Route::post('apiv2/kalkulasishu','Apiv2Controller@kalkulasishu');

Route::post('apiv2/getshuaktif','Apiv2Controller@getshuaktif');

Route::post('apiv2/getshuwhere','Apiv2Controller@getshuwhere');

Route::post('apiv2/resetshu','Apiv2Controller@resetshu');


//suplier

Route::post('apiv2/getsuplier','Apiv2Controller@getsuplier');

Route::post('apiv2/insertsuplier','Apiv2Controller@insertsuplier');

Route::post('apiv2/editsuplier','Apiv2Controller@editsuplier');

Route::post('apiv2/updatesuplier','Apiv2Controller@updatesuplier');

Route::post('apiv2/deletesuplier','Apiv2Controller@deletesuplier');



//pelanggan

Route::post('apiv2/getpelanggan','Apiv2Controller@getpelanggan');

Route::post('apiv2/insertpelanggan','Apiv2Controller@insertpelanggan');

Route::post('apiv2/editpelanggan','Apiv2Controller@editpelanggan');

Route::post('apiv2/updatepelanggan','Apiv2Controller@updatepelanggan');

Route::post('apiv2/deletepelanggan','Apiv2Controller@deletepelanggan');



//pembelian

Route::post('apiv2/getpembelian','Apiv2Controller@getpembelian');

Route::post('apiv2/insertpembelian','Apiv2Controller@insertpembelian');

Route::post('apiv2/editpembelian','Apiv2Controller@editpembelian');

Route::post('apiv2/updatepembelian','Apiv2Controller@updatepembelian');

Route::post('apiv2/deletepembelian','Apiv2Controller@deletepembelian');



//pembeliandetail

Route::post('apiv2/getpembeliandetail','Apiv2Controller@getpembeliandetail');

Route::post('apiv2/insertpembeliandetail','Apiv2Controller@insertpembeliandetail');

Route::post('apiv2/editpembeliandetail','Apiv2Controller@editpembeliandetail');

Route::post('apiv2/updatepembeliandetail','Apiv2Controller@updatepembeliandetail');

Route::post('apiv2/deletepembeliandetail','Apiv2Controller@deletepembeliandetail');



//kategori


Route::post('apiv2/getkategori','Apiv2Controller@getkategori');

Route::post('apiv2/insertkategori','Apiv2Controller@insertkategori');

Route::post('apiv2/editkategori','Apiv2Controller@editkategori');

Route::post('apiv2/updatekategori','Apiv2Controller@updatekategori');

Route::post('apiv2/deletekategori','Apiv2Controller@deletekategori');

//Produk


Route::post('apiv2/getproduk','Apiv2Controller@getproduk');

Route::post('apiv2/insertproduk','Apiv2Controller@insertproduk');

Route::post('apiv2/editproduk','Apiv2Controller@editproduk');

Route::post('apiv2/updateproduk','Apiv2Controller@updateproduk');

Route::post('apiv2/deleteproduk','Apiv2Controller@deleteproduk');



Route::post('apiv2/gettahunoperasiaktif','Apiv2Controller@gettahunoperasiaktif');


// Pesan Koperasi

Route::post('apiv2/getnewpesankementerian','Apiv2Controller@getnewpesankementerian');

Route::post('apiv2/getallpesankementerian','Apiv2Controller@getallpesankementerian');

Route::post('apiv2/getdetailpesankementerian','Apiv2Controller@getdetailpesankementerian');

Route::post('apiv2/updatestatuspesan','Apiv2Controller@updatestatuspesan');

Route::post('apiv2/deletepesankementerian','Apiv2Controller@deletepesankementerian');



//Training Kementerian

Route::post('apiv2/gettrainingkementerian','Apiv2Controller@gettrainingkementerian');

Route::post('apiv2/gettrainingkementerianwhere','Apiv2Controller@gettrainingkementerianwhere');

Route::post('apiv2/showpesertatrainingkementerian','Apiv2Controller@showpesertatrainingkementerian');

Route::post('apiv2/updatepesertatrainingkementerian','Apiv2Controller@updatepesertatrainingkementerian');

Route::post('apiv2/deletepesertatrainingkementerian','Apiv2Controller@deletepesertatrainingkementerian');




//Seminar kementerian
Route::post('apiv2/getseminarkementerian','Apiv2Controller@getseminarkementerian');

Route::post('apiv2/getseminarkementerianwhere','Apiv2Controller@getseminarkementerianwhere');

Route::post('apiv2/showpesertaseminarkementerian','Apiv2Controller@showpesertaseminarkementerian');

Route::post('apiv2/updatepesertaseminarkementerian','Apiv2Controller@updatepesertaseminarkementerian');

Route::post('apiv2/deletepesertaseminarkementerian','Apiv2Controller@deletepesertaseminarkementerian');



Route::post('apiv2/getreporttransaksi','Apiv2Controller@getreporttransaksi');

Route::post('apiv2/getreporttransaksitoday','Apiv2Controller@getreporttransaksitoday');

Route::post('apiv2/getreporttransaksirange','Apiv2Controller@getreporttransaksirange');

Route::post('apiv2/getreporttransaksidetail','Apiv2Controller@getreporttransaksidetail');



//PENJUALAN


Route::post('apiv2/getprodukbykategori','Apiv2Controller@getprodukbykategori');

Route::post('apiv2/getprodukwhere','Apiv2Controller@getprodukwhere');

Route::post('apiv2/getprodukdetail','Apiv2Controller@getprodukdetail');



Route::post('apiv2/inserttransaksi','Apiv2Controller@inserttransaksi');


Route::post('apiv2/getmydetailtemp','Apiv2Controller@getmydetailtemp');

Route::post('apiv2/insertdetailtemp','Apiv2Controller@insertdetailtemp');

Route::post('apiv2/editdetailtemp','Apiv2Controller@editdetailtemp');

Route::post('apiv2/updatedetailtemp','Apiv2Controller@updatedetailtemp');

Route::post('apiv2/deletedetailtemp','Apiv2Controller@deletedetailtemp');




Route::post('apiv2/checkout','Apiv2Controller@checkout');

Route::post('apiv2/canceltrans','Apiv2Controller@canceltrans');

Route::post('apiv2/pembayaran','Apiv2Controller@pembayaran');




/* -------------------- APIv3 (api utk web kementerian) -------------------------- */

Route::post('apiv3/loginadmin','apiv3Controller@loginadmin');

Route::post('apiv3/logout','apiv3Controller@logout');

Route::post('apiv3/getprofile','apiv3Controller@getprofile');

Route::post('apiv3/updateprofile','apiv3Controller@updateprofile');



// Alamat------------------------------------------------------------------//

Route::get('apiv3/getprovinsi','Apiv3Controller@getprovinsi');

Route::post('apiv3/getkabupatenkota','Apiv3Controller@getkabupatenkota');

Route::post('apiv3/getkecamatan','Apiv3Controller@getkecamatan');

Route::post('apiv3/getkelurahan','Apiv3Controller@getkelurahan');



// Admin Kementerian
Route::post('apiv3/getadminkementerian','apiv3Controller@getadminkementerian');

Route::post('apiv3/insertadminkementerian','apiv3Controller@insertadminkementerian');

Route::post('apiv3/editadminkementerian','apiv3Controller@editadminkementerian');

Route::post('apiv3/updateadminkementerian','apiv3Controller@updateadminkementerian');

Route::post('apiv3/deleteadminkementerian','apiv3Controller@deleteadminkementerian');

Route::post('apiv3/getakseskementerian','apiv3Controller@getakseskementerian');




//info kementerian

Route::post('apiv3/getinfokementerian','apiv3Controller@getinfokementerian');

Route::post('apiv3/insertinfokementerian','apiv3Controller@insertinfokementerian');

Route::post('apiv3/editinfokementerian','apiv3Controller@editinfokementerian');

Route::post('apiv3/updateinfokementerian','apiv3Controller@updateinfokementerian');

Route::post('apiv3/deleteinfokementerian','apiv3Controller@deleteinfokementerian');


//Training kementerian

Route::post('apiv3/gettrainingkementerian','apiv3Controller@gettrainingkementerian');

Route::post('apiv3/inserttrainingkementerian','apiv3Controller@inserttrainingkementerian');

Route::post('apiv3/edittrainingkementerian','apiv3Controller@edittrainingkementerian');

Route::post('apiv3/updatetrainingkementerian','apiv3Controller@updatetrainingkementerian');

Route::post('apiv3/deletetrainingkementerian','apiv3Controller@deletetrainingkementerian');


//Booking

Route::post('apiv3/getbookingtrainingkementerian','apiv3Controller@getbookingtrainingkementerian');

Route::post('apiv3/getbookingtrainingkementerianwhere','apiv3Controller@getbookingtrainingkementerianwhere');

Route::post('apiv3/getbookingseminarkementerian','apiv3Controller@getbookingseminarkementerian');

Route::post('apiv3/getbookingseminarkementerianwhere','apiv3Controller@getbookingseminarkementerianwhere');


//Seminar kementerian

Route::post('apiv3/getseminarkementerian','Apiv3Controller@getseminarkementerian');

Route::post('apiv3/insertseminarkementerian','Apiv3Controller@insertseminarkementerian');

Route::post('apiv3/editseminarkementerian','Apiv3Controller@editseminarkementerian');

Route::post('apiv3/updateseminarkementerian','Apiv3Controller@updateseminarkementerian');

Route::post('apiv3/deleteseminarkementerian','Apiv3Controller@deleteseminarkementerian');




//Koperasi

Route::post('apiv3/getkoperasilist','apiv3Controller@getkoperasilist');

Route::post('apiv3/getskalakoperasi','apiv3Controller@getskalakoperasi');

Route::post('apiv3/gettipekoperasi','apiv3Controller@gettipekoperasi');

Route::post('apiv3/getkoperasibykategori','apiv3Controller@getkoperasibykategori');


// Admin Koperasi
Route::post('apiv3/getadminkoperasi','apiv3Controller@getadminkoperasi');

Route::post('apiv3/insertadminkoperasi','apiv3Controller@insertadminkoperasi');

Route::post('apiv3/editadminkoperasi','apiv3Controller@editadminkoperasi');

Route::post('apiv3/updateadminkoperasi','apiv3Controller@updateadminkoperasi');

Route::post('apiv3/deleteadminkoperasi','apiv3Controller@deleteadminkoperasi');



// Koperasi
Route::post('apiv3/getkoperasi','apiv3Controller@getkoperasi');

Route::post('apiv3/insertkoperasi','apiv3Controller@insertkoperasi');

Route::post('apiv3/editkoperasi','apiv3Controller@editkoperasi');

Route::post('apiv3/updatekoperasi','apiv3Controller@updatekoperasi');

Route::post('apiv3/deletekoperasi','apiv3Controller@deletekoperasi');



// Pesan Kementerian
Route::post('apiv3/getpesankementerian','apiv3Controller@getpesankementerian');

Route::post('apiv3/insertpesankementerian','apiv3Controller@insertpesankementerian');

Route::post('apiv3/editpesankementerian','apiv3Controller@editpesankementerian');

Route::post('apiv3/updatepesankementerian','apiv3Controller@updatepesankementerian');

Route::post('apiv3/deletepesankementerian','apiv3Controller@deletepesankementerian');



// Transaksi
Route::post('apiv3/gettransaksi','apiv3Controller@gettransaksi');

Route::post('apiv3/gettransaksirange','apiv3Controller@gettransaksirange');

Route::post('apiv3/getjumlahtransaksi','apiv3Controller@getjumlahtransaksi');

Route::post('apiv3/getjumlahtransaksirange','apiv3Controller@getjumlahtransaksirange');

Route::post('apiv3/getjumlahkoperasi','apiv3Controller@getjumlahkoperasi');

Route::post('apiv3/getjumlahanggota','apiv3Controller@getjumlahanggota');


Route::post('apiv3/getjumlahtransaksihariini','apiv3Controller@getjumlahtransaksihariini');
Route::post('apiv3/gettransaksihariini','apiv3Controller@gettransaksihariini');



Route::post('apiv3/getmemberbykategori','apiv3Controller@getmemberbykategori');

//Route::post('apiv3/getmemberbykategori','apiv3Controller@getmemberbykategori');





/*-----------------------
-----------S_api secure----------------------------*/


Route::post('s_api/gettransaksixyz','apisController@gettransaksixyz');
Route::post('s_api/updatetransaksixyz','apisController@updatetransaksixyz');