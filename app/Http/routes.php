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
Route::get('api/statserver', function () {
    return Response::json(['status' => 1, 'message' => 'Hello, Urip Ki']);
});
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
Route::post('apiv2/registrasikoperasi','Apiv2Controller@registrasikoperasi');

//koperasi
Route::post('apiv2/getmykoperasi','Apiv2Controller@getmykoperasi');
Route::post('apiv2/updatemykoperasi','Apiv2Controller@updatemykoperasi');

//Administrasi Koperasi
Route::post('apiv2/updatekelengkapanadmin','Apiv2Controller@updatekelengkapanadmin');


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

Route::get('apiv2/getkelompokkop','Apiv2Controller@getkelompokkop');
Route::get('apiv2/getsektorusaha','Apiv2Controller@getsektorusaha');

//Koperasi Sekunder
Route::get('apiv2/getkoperasi','Apiv2Controller@getkoperasi');
Route::post('apiv2/insertkoperasisekunder','Apiv2Controller@insertkoperasisekunder');
Route::post('apiv2/getkoperasisekunder','Apiv2Controller@getkoperasisekunder');
Route::post('apiv2/keluarkoperasisekunder','Apiv2Controller@keluarkoperasisekunder');

//Sistem Informasi Debitur
Route::post('apiv2/getanggotabermasalah','Apiv2Controller@getanggotabermasalah');
Route::post('apiv2/insertanggotabermasalah','Apiv2Controller@insertanggotabermasalah');
Route::post('apiv2/hapusanggotabermasalah','Apiv2Controller@hapusanggotabermasalah');

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



//RAT


Route::post('apiv2/getrat','Apiv2Controller@getrat');

Route::post('apiv2/insertrat','Apiv2Controller@insertrat');

Route::post('apiv2/editrat','Apiv2Controller@editrat');

Route::post('apiv2/updaterat','Apiv2Controller@updaterat');

Route::post('apiv2/deleterat','Apiv2Controller@deleterat');


Route::post('apiv2/getkehadiranrataktif','Apiv2Controller@getkehadiranrataktif');






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

Route::post('apiv3/loginadmin','Apiv3Controller@loginadmin');

Route::post('apiv3/logout','Apiv3Controller@logout');

Route::post('apiv3/getprofile','Apiv3Controller@getprofile');

Route::post('apiv3/updateprofile','Apiv3Controller@updateprofile');



// Alamat------------------------------------------------------------------//

Route::get('apiv3/getprovinsi','Apiv3Controller@getprovinsi');

Route::post('apiv3/getkabupatenkota','Apiv3Controller@getkabupatenkota');

Route::post('apiv3/getkecamatan','Apiv3Controller@getkecamatan');

Route::post('apiv3/getkelurahan','Apiv3Controller@getkelurahan');



// Admin Kementerian
Route::post('apiv3/getadminkementerian','Apiv3Controller@getadminkementerian');

Route::post('apiv3/insertadminkementerian','Apiv3Controller@insertadminkementerian');

Route::post('apiv3/editadminkementerian','Apiv3Controller@editadminkementerian');

Route::post('apiv3/updateadminkementerian','Apiv3Controller@updateadminkementerian');

Route::post('apiv3/deleteadminkementerian','Apiv3Controller@deleteadminkementerian');

Route::post('apiv3/getakseskementerian','Apiv3Controller@getakseskementerian');




//info kementerian

Route::post('apiv3/getinfokementerian','Apiv3Controller@getinfokementerian');

Route::post('apiv3/insertinfokementerian','Apiv3Controller@insertinfokementerian');

Route::post('apiv3/editinfokementerian','Apiv3Controller@editinfokementerian');

Route::post('apiv3/updateinfokementerian','Apiv3Controller@updateinfokementerian');

Route::post('apiv3/deleteinfokementerian','Apiv3Controller@deleteinfokementerian');


//Training kementerian

Route::post('apiv3/gettrainingkementerian','Apiv3Controller@gettrainingkementerian');

Route::post('apiv3/inserttrainingkementerian','Apiv3Controller@inserttrainingkementerian');

Route::post('apiv3/edittrainingkementerian','Apiv3Controller@edittrainingkementerian');

Route::post('apiv3/updatetrainingkementerian','Apiv3Controller@updatetrainingkementerian');

Route::post('apiv3/deletetrainingkementerian','Apiv3Controller@deletetrainingkementerian');


//Booking

Route::post('apiv3/getbookingtrainingkementerian','Apiv3Controller@getbookingtrainingkementerian');

Route::post('apiv3/getbookingtrainingkementerianwhere','Apiv3Controller@getbookingtrainingkementerianwhere');

Route::post('apiv3/getbookingseminarkementerian','Apiv3Controller@getbookingseminarkementerian');

Route::post('apiv3/getbookingseminarkementerianwhere','Apiv3Controller@getbookingseminarkementerianwhere');


//Seminar kementerian

Route::post('apiv3/getseminarkementerian','Apiv3Controller@getseminarkementerian');

Route::post('apiv3/insertseminarkementerian','Apiv3Controller@insertseminarkementerian');

Route::post('apiv3/editseminarkementerian','Apiv3Controller@editseminarkementerian');

Route::post('apiv3/updateseminarkementerian','Apiv3Controller@updateseminarkementerian');

Route::post('apiv3/deleteseminarkementerian','Apiv3Controller@deleteseminarkementerian');




//Koperasi

Route::post('apiv3/getkoperasilist','Apiv3Controller@getkoperasilist');

Route::post('apiv3/getskalakoperasi','Apiv3Controller@getskalakoperasi');

Route::post('apiv3/gettipekoperasi','Apiv3Controller@gettipekoperasi');

Route::post('apiv3/getkoperasibykategori','Apiv3Controller@getkoperasibykategori');


// Admin Koperasi
Route::post('apiv3/getadminkoperasi','Apiv3Controller@getadminkoperasi');

Route::post('apiv3/insertadminkoperasi','Apiv3Controller@insertadminkoperasi');

Route::post('apiv3/editadminkoperasi','Apiv3Controller@editadminkoperasi');

Route::post('apiv3/updateadminkoperasi','Apiv3Controller@updateadminkoperasi');

Route::post('apiv3/deleteadminkoperasi','Apiv3Controller@deleteadminkoperasi');



Route::post('apiv3/getsuperadmin','Apiv3Controller@getsuperadmin');



// Koperasi
Route::post('apiv3/getkoperasi','Apiv3Controller@getkoperasi');

Route::post('apiv3/insertkoperasi','Apiv3Controller@insertkoperasi');

Route::post('apiv3/editkoperasi','Apiv3Controller@editkoperasi');

Route::post('apiv3/updatekoperasi','Apiv3Controller@updatekoperasi');

Route::post('apiv3/deletekoperasi','Apiv3Controller@deletekoperasi');



// Pesan Kementerian
Route::post('apiv3/getpesankementerian','Apiv3Controller@getpesankementerian');

Route::post('apiv3/insertpesankementerian','Apiv3Controller@insertpesankementerian');

Route::post('apiv3/editpesankementerian','Apiv3Controller@editpesankementerian');

Route::post('apiv3/updatepesankementerian','Apiv3Controller@updatepesankementerian');

Route::post('apiv3/deletepesankementerian','Apiv3Controller@deletepesankementerian');



// Transaksi
Route::post('apiv3/gettransaksi','Apiv3Controller@gettransaksi');

Route::post('apiv3/gettransaksirange','Apiv3Controller@gettransaksirange');

Route::post('apiv3/getjumlahtransaksi','Apiv3Controller@getjumlahtransaksi');

Route::post('apiv3/getjumlahtransaksirange','Apiv3Controller@getjumlahtransaksirange');

Route::post('apiv3/getjumlahkoperasi','Apiv3Controller@getjumlahkoperasi');

Route::post('apiv3/getjumlahanggota','Apiv3Controller@getjumlahanggota');


Route::post('apiv3/getjumlahtransaksihariini','Apiv3Controller@getjumlahtransaksihariini');
Route::post('apiv3/gettransaksihariini','Apiv3Controller@gettransaksihariini');



Route::post('apiv3/getmemberbykategori','Apiv3Controller@getmemberbykategori');

//Route::post('apiv3/getmemberbykategori','apiv3Controller@getmemberbykategori');


/*-----------------APIs Created by Andreas for new features-----------------*/

// Password Reset Admin Koperasi
Route::post('apiv4/forgotpassword','Apiv4Controller@forgotpassword');

Route::post('apiv4/resetpassword','Apiv4Controller@resetpassword');


// Simpanan berjangka
Route::post('apiv4/getsimpananberjangka','Apiv4Controller@getsimpananberjangka');

Route::post('apiv4/insertsimpananberjangka','Apiv4Controller@insertsimpananberjangka');

Route::post('apiv4/ambilsimka','Apiv4Controller@ambilsimka');

Route::post('apiv4/perpanjangsimka','Apiv4Controller@perpanjangsimka');

Route::post('apiv4/editsimpananberjangka','Apiv4Controller@editsimpananberjangka');

Route::post('apiv4/updatesimpananberjangka','Apiv4Controller@updatesimpananberjangka');


//Simpanan Berjangka Report
Route::post('apiv4/getsimpananberjangkahari','Apiv4Controller@getsimpananberjangkahari');

Route::post('apiv4/getsimpananberjangkaminggu','Apiv4Controller@getsimpananberjangkaminggu');

Route::post('apiv4/getsimpananberjangkabulan','Apiv4Controller@getsimpananberjangkabulan');


// Setting simka
Route::post('apiv4/getsetingsimka','Apiv4Controller@getsetingsimka');

Route::post('apiv4/insertsetingsimka','Apiv4Controller@insertsetingsimka');

Route::post('apiv4/editsetingsimka','Apiv4Controller@editsetingsimka');

Route::post('apiv4/updatesetingsimka','Apiv4Controller@updatesetingsimka');

Route::post('apiv4/deletesetingsimka','Apiv4Controller@deletesetingsimka');


//pengambilan simka
Route::post('apiv4/getpengambilansimka','Apiv4Controller@getpengambilansimka');

Route::post('apiv4/insertpengambilansimka','Apiv4Controller@insertpengambilansimka');

Route::post('apiv4/editpengambilansimka','Apiv4Controller@editpengambilansimka');

Route::post('apiv4/updatepengambilansimka','Apiv4Controller@updatepengambilansimka');

Route::post('apiv4/deletepengambilansimka','Apiv4Controller@deletepengambilansimka');


//minimal simpanan
Route::post('apiv4/getminimalsimpanan','Apiv4Controller@getminimalsimpanan');

Route::post('apiv4/insertminimalsimpanan','Apiv4Controller@insertminimalsimpanan');

Route::post('apiv4/editminimalsimpanan','Apiv4Controller@editminimalsimpanan');

Route::post('apiv4/updateminimalsimpanan','Apiv4Controller@updateminimalsimpanan');

Route::post('apiv4/deleteminimalsimpanan','Apiv4Controller@deleteminimalsimpanan');


//metode
Route::post('apiv4/getmetode','Apiv4Controller@getmetode');

Route::post('apiv4/insertmetode','Apiv4Controller@insertmetode');

Route::post('apiv4/editmetode','Apiv4Controller@editmetode');

Route::post('apiv4/updatemetode','Apiv4Controller@updatemetode');

Route::post('apiv4/deletemetode','Apiv4Controller@deletemetode');


//Tabungan
Route::post('apiv4/gettabungan','Apiv4Controller@gettabungan');

Route::post('apiv4/gettabungandetail','Apiv4Controller@gettabungandetail');

Route::post('apiv4/inserttabungan','Apiv4Controller@inserttabungan');

Route::post('apiv4/ambiltabungan','Apiv4Controller@ambiltabungan');

Route::post('apiv4/getsetingtabungan','Apiv4Controller@getsetingtabungan');


//Suku bunga
Route::post('apiv4/insertsukubunga','Apiv4Controller@insertsukubunga');

Route::post('apiv4/editsukubunga','Apiv4Controller@editsukubunga');

Route::post('apiv4/updatesukubunga','Apiv4Controller@updatesukubunga');

Route::post('apiv4/deletesukubunga','Apiv4Controller@deletesukubunga');


//Administrasi Tabungan
Route::post('apiv4/insertadministrasi','Apiv4Controller@insertadministrasi');

Route::post('apiv4/editadministrasitabungan','Apiv4Controller@editadministrasitabungan');

Route::post('apiv4/updateadministrasitabungan','Apiv4Controller@updateadministrasitabungan');

Route::post('apiv4/updateperhitunganbungatiapbulan','Apiv4Controller@updateperhitunganbungatiapbulan');


//Tabungan Report
Route::post('apiv4/gettabungankoperasihari','Apiv4Controller@gettabungankoperasihari');

Route::post('apiv4/gettabungankoperasiminggu','Apiv4Controller@gettabungankoperasiminggu');

Route::post('apiv4/gettabungankoperasibulan','Apiv4Controller@gettabungankoperasibulan');


//Peminjaman
Route::post('apiv4/getpeminjaman','Apiv4Controller@getpeminjaman');

Route::post('apiv4/getpeminjamandetail','Apiv4Controller@getpeminjamandetail');

Route::post('apiv4/prosespeminjamandetail','Apiv4Controller@prosespeminjamandetail');

Route::post('apiv4/insertpeminjaman','Apiv4Controller@insertpeminjaman');

Route::post('apiv4/getsetingpeminjaman','Apiv4Controller@getsetingpeminjaman');

Route::post('apiv4/insertsetingpeminjaman','Apiv4Controller@insertsetingpeminjaman');

Route::post('apiv4/editsetingpeminjaman','Apiv4Controller@editsetingpeminjaman');

Route::post('apiv4/updatesetingpeminjaman','Apiv4Controller@updatesetingpeminjaman');

Route::post('apiv4/deletesetingpeminjaman','Apiv4Controller@deletesetingpeminjaman');


//Peminjaman Report
Route::post('apiv4/getpeminjamanhari','Apiv4Controller@getpeminjamanhari');

Route::post('apiv4/getpeminjamanminggu','Apiv4Controller@getpeminjamanminggu');

Route::post('apiv4/getpeminjamanbulan','Apiv4Controller@getpeminjamanbulan');


//Minimal Simpanan
Route::post('apiv4/getminimalpeminjaman','Apiv4Controller@getminimalpeminjaman');

Route::post('apiv4/insertminimalpeminjaman','Apiv4Controller@insertminimalpeminjaman');

Route::post('apiv4/updateminimalpeminjaman','Apiv4Controller@updateminimalpeminjaman');


//Tipe bunga
Route::post('apiv4/gettipebunga','Apiv4Controller@gettipebunga');

Route::post('apiv4/gettipebungadetail','Apiv4Controller@gettipebungadetail');


// Iuran wajib website
Route::post('apiv4/getiuranwajib','Apiv4Controller@getiuranwajib');

Route::post('apiv4/getiuranwajibdetail','Apiv4Controller@getiuranwajibdetail');

Route::post('apiv4/insertiuranwajib','Apiv4Controller@insertiuranwajib');


//Komentar Koperasi
Route::post('apiv4/getkomentar','Apiv4Controller@getkomentar');

Route::post('apiv4/deletekomentarinformasikoperasi','Apiv4Controller@deletekomentarinformasikoperasi');


//Insert Anggotakoperasi
Route::post('apiv4/insertanggotakoperasi','Apiv4Controller@insertanggotakoperasi');

Route::post('apiv4/updateanggotakoperasi','Apiv4Controller@updateanggotakoperasi');


//Notifikasi Info Koperasi
Route::post('apiv4/getnotifinfokoperasi','Apiv4Controller@getnotifinfokoperasi');

Route::post('apiv4/insertviewerinfokoperasi','Apiv4Controller@insertviewerinfokoperasi');

Route::post('apiv4/updatenotifseminarkoperasi','Apiv4Controller@updatenotifseminarkoperasi');

Route::post('apiv4/updatenotiftrainingkoperasi','Apiv4Controller@updatenotiftrainingkoperasi');


//Pembelian
Route::post('apiv4/insertpembeliantemp','Apiv4Controller@insertpembeliantemp');

Route::post('apiv4/insertpembeliandetailtemp','Apiv4Controller@insertpembeliandetailtemp');

Route::post('apiv4/getpembeliandetailtemp','Apiv4Controller@getpembeliandetailtemp');

Route::post('apiv4/insertpembelian','Apiv4Controller@insertpembelian');

Route::post('apiv4/deletepembeliantemp','Apiv4Controller@deletepembeliantemp');

Route::post('apiv4/deletepembelian','Apiv4Controller@deletepembelian');


//Notifikasi
Route::post('apiv4/updatenotifikasi','Apiv4Controller@updatenotifikasi');

//Simpanan Pokok
Route::post('apiv4/getsimpananpokok','Apiv4Controller@getsimpananpokok');

//Route::post('apiv4/insertsimpananpokok','Apiv4Controller@insertsimpananpokok');

//Route::post('apiv4/editsimpananpokok','Apiv4Controller@editsimpananpokok');

//Route::post('apiv4/updatesimpananpokok','Apiv4Controller@updatesimpananpokok');

//Route::post('apiv4/deletesimpananpokok','Apiv4Controller@deletesimpananpokok');


// Iuran wajib mobile
Route::post('apiv5/getiuranwajibmember','Apiv5Controller@getiuranwajibmember');

Route::post('apiv5/getiuranwajibmemberssp','Apiv5Controller@getiuranwajibmemberssp');

Route::post('apiv5/insertiuranwajibanggota','Apiv5Controller@insertiuranwajibanggota');


//Get Token Mobile
Route::post('apiv5/ambiltoken','Apiv5Controller@ambiltoken');


//Get Simpanan Berjangka Mobile
Route::post('apiv5/getsimpananberjangka','Apiv5Controller@getsimpananberjangka');

Route::post('apiv5/getsimpananberjangkadetail','Apiv5Controller@getsimpananberjangkadetail');


//Tabungan Mobile
Route::post('apiv5/gettabungandetail','Apiv5Controller@gettabungandetail');

Route::post('apiv5/getrinciantabunganssp','Apiv5Controller@getrinciantabunganssp');

Route::post('apiv5/inserttabunganssp','Apiv5Controller@inserttabunganssp');


//Peminjaman Mobile
Route::post('apiv5/getpeminjaman','Apiv5Controller@getpeminjaman');

Route::post('apiv5/getpeminjamandetail','Apiv5Controller@getpeminjamandetail');

Route::post('apiv5/getpeminjamanrincian','Apiv5Controller@getpeminjamanrincian');

Route::post('apiv5/getpeminjamanrincianssp','Apiv5Controller@getpeminjamanrincianssp');

Route::post('apiv5/prosespeminjamandetailssp','Apiv5Controller@prosespeminjamandetailssp');


Route::post('apiv5/getprofile','Apiv5Controller@getprofile');


// Grafik Dashboard Kementrian
Route::post('apiv6/gettransaksikoperasikonsumsi','Apiv6Controller@gettransaksikoperasikonsumsi');

Route::post('apiv6/gettransaksikoperasikonsumsihariini','Apiv6Controller@gettransaksikoperasikonsumsihariini');

Route::post('apiv6/gettransaksikoperasisimpanpinjam','Apiv6Controller@gettransaksikoperasisimpanpinjam');

Route::post('apiv6/gettransaksikoperasisimpanpinjamhariini','Apiv6Controller@gettransaksikoperasisimpanpinjamhariini');

Route::post('apiv6/getgrafikjumlahkoperasi','Apiv6Controller@getgrafikjumlahkoperasi');

Route::post('apiv6/getgrafikjumlahkoperasihariini','Apiv6Controller@getgrafikjumlahkoperasihariini');

Route::post('apiv6/getgrafikjumlahanggota','Apiv6Controller@getgrafikjumlahanggota');

Route::post('apiv6/getgrafikjumlahanggotahariini','Apiv6Controller@getgrafikjumlahanggotahariini');

Route::post('apiv6/getpetakoperasi','Apiv6Controller@getpetakoperasi');

Route::post('apiv6/getgrafikjumlahkoperasiperbulan','Apiv6Controller@getgrafikjumlahkoperasiperbulan');

Route::post('apiv6/getgrafikjumlahkoperasipertahun','Apiv6Controller@getgrafikjumlahkoperasipertahun');

Route::post('apiv6/getgrafiktransaksikoperasikonsumsiperbulan','Apiv6Controller@getgrafiktransaksikoperasikonsumsiperbulan');

Route::post('apiv6/getgrafiktransaksikoperasikonsumsipertahun','Apiv6Controller@getgrafiktransaksikoperasikonsumsipertahun');

Route::post('apiv6/getgrafiktransaksikoperasisimpanpinjam','Apiv6Controller@getgrafiktransaksikoperasisimpanpinjam');

Route::post('apiv6/getgrafiktransaksikoperasisimpanpinjampertahun','Apiv6Controller@getgrafiktransaksikoperasisimpanpinjampertahun');

Route::post('apiv6/getjumlahanggotaperbulan','Apiv6Controller@getjumlahanggotaperbulan');

Route::post('apiv6/getjumlahanggotapertahun','Apiv6Controller@getjumlahanggotapertahun');


//tipe koperasi kementrian
Route::post('apiv6/gettipekopeasi','Apiv6Controller@gettipekopeasi');


//Forgot & Reset Passwword
Route::post('apiv6/forgotpassword','Apiv6Controller@forgotpassword');

Route::post('apiv6/resetpassword','Apiv6Controller@resetpassword');


/*--------------End APIs Created by Andreas for new features----------------*/


/*----------------------------------S_api secure----------------------------*/

Route::post('s_api/gettransaksixyz','ApisController@gettransaksixyz');
Route::post('s_api/updatetransaksixyz','ApisController@updatetransaksixyz');

/*--------------APIs Created by Timothy for BMT and APEX modules-----------------*/
//Dewan Pengawas Syariah
Route::post('apiv7/insertdewanpengawas','Apiv7Controller@insertdewanpengawas');

Route::post('apiv7/getdewanpengawas','Apiv7Controller@getdewanpengawas');
/*-----------End APIs Created by Timothy for BMT and APEX modules-----------------*/

/*--------------APIs Created by fahri-----------------*/
//pengurus
Route::post('apiv7/getpengurus','Apiv7Controller@getpengurus');
Route::post('apiv7/insertpengurus','Apiv7Controller@insertpengurus');
Route::post('apiv7/deletepengurus','Apiv7Controller@deletepengurus');
Route::post('apiv7/updatepengurus','Apiv7Controller@updatepengurus');

//pengawas
Route::post('apiv7/insertpengawas','Apiv7Controller@insertpengawas');
Route::post('apiv7/deletepengawas','Apiv7Controller@deletepengawas');
Route::post('apiv7/updatepengawas','Apiv7Controller@updatepengawas');

//pembiayaan
Route::post('apiv7/getpembiayaan','Apiv7Controller@getpembiayaan');
Route::post('apiv7/getpembiayaanby','Apiv7Controller@getpembiayaanby');
Route::post('apiv7/insertpembiayaan','Apiv7Controller@insertpembiayaan');
Route::post('apiv7/updatepembiayaan','Apiv7Controller@updatepembiayaan');
Route::post('apiv7/deletepembiayaan','Apiv7Controller@deletepembiayaan');

//pembiayaansyariah
Route::post('apiv7/getpembiayaansyariah','Apiv7Controller@getpembiayaansyariah');
Route::post('apiv7/getpembiayaansyariahby','Apiv7Controller@getpembiayaansyariahby');
Route::post('apiv7/insertpembiayaansyariah','Apiv7Controller@insertpembiayaansyariah');
Route::post('apiv7/updatepembiayaansyariah','Apiv7Controller@updatepembiayaansyariah');
Route::post('apiv7/deletepembiayaansyariah','Apiv7Controller@deletepembiayaansyariah');

//tabunganproduk
Route::post('apiv7/gettabunganproduk','Apiv7Controller@gettabunganproduk');
Route::post('apiv7/inserttabunganproduk','Apiv7Controller@inserttabunganproduk');
Route::post('apiv7/updatetabunganproduk','Apiv7Controller@updatetabunganproduk');
Route::post('apiv7/deletetabunganproduk','Apiv7Controller@deletetabunganproduk');
//tabungansyariah
Route::post('apiv7/inserttabungansyariah','Apiv7Controller@inserttabungansyariah');
Route::post('apiv7/inserttabungansyariahmasuk','Apiv7Controller@inserttabungansyariahmasuk');
Route::post('apiv7/ambiltabungansyariah','Apiv7Controller@ambiltabungansyariah');
Route::post('apiv7/gettabungansyariah','Apiv7Controller@gettabungansyariah');
Route::post('apiv7/deletetabungansyariah','Apiv7Controller@deletetabungansyariah');
Route::post('apiv7/updatetabungansyariah','Apiv7Controller@updatetabungansyariah');
Route::post('apiv7/getmutasi','Apiv7Controller@getmutasi');

Route::post('apiv7/cobasession','Apiv7Controller@cobasession');
Route::post('apiv7/cobasession2','Apiv7Controller@cobasession2');
Route::post('apiv7/cobaliat','Apiv7Controller@cobaliat');

//pembiayaanyariahdetail
Route::post('apiv7/insertpembiayaansyariahdetail','Apiv7Controller@insertpembiayaansyariahdetail');


/*--------------APIs Created by Timothy for RMinistry report modules-----------------*/

Route::post('apiv8/insertsusunankepengurusan','Apiv8Controller@insertsusunankepengurusan');

Route::post('apiv8/deletesusunankepengurusan','Apiv8Controller@deletesusunankepengurusan');

Route::post('apiv8/getketuakoperasi','Apiv8Controller@getketuakoperasi');

Route::post('apiv8/getsekretariskoperasi','Apiv8Controller@getsekretariskoperasi');

Route::post('apiv8/getbendaharakoperasi','Apiv8Controller@getbendaharakoperasi');

Route::post('apiv8/getpengawaskoperasi','Apiv8Controller@getpengawaskoperasi');

Route::post('apiv8/getmanajerkoperasi','Apiv8Controller@getmanajerkoperasi');

Route::post('apiv8/getidentitaskoperasi','Apiv8Controller@getidentitaskoperasi');
Route::post('apiv8/getisektorusaha','Apiv8Controller@getisektorusaha');

Route::post('apiv8/getidentitaskoperasikementrian','Apiv8Controller@getidentitaskoperasi');

Route::post('apiv8/insertidentitaskoperasi','Apiv8Controller@insertidentitaskoperasi');
//Indikator Usaha CRUDS
Route::post('apiv8/getindikatorusaha','Apiv8Controller@getindikatorusaha');

Route::post('apiv8/getindikatorusahaby','Apiv8Controller@getindikatorusahaby');

Route::post('apiv8/insertindikatorusaha','Apiv8Controller@insertindikatorusaha');

Route::post('apiv8/updateindikatorusaha','Apiv8Controller@updateindikatorusaha');

Route::post('apiv8/deleteindikatorusaha','Apiv8Controller@deleteindikatorusaha');
//Karyawan CRUDS
Route::post('apiv8/getkaryawan','Apiv8Controller@getkaryawan');

Route::post('apiv8/getkaryawanby','Apiv8Controller@getkaryawanby');

Route::post('apiv8/insertkaryawan','Apiv8Controller@insertkaryawan');

Route::post('apiv8/updatekaryawan','Apiv8Controller@updatekaryawan');

Route::post('apiv8/deletekaryawan','Apiv8Controller@deletekaryawan');
//Anggaran CRUDS
Route::post('apiv8/getanggarandasar','Apiv8Controller@getanggarandasar');

Route::post('apiv8/getanggarandasarby','Apiv8Controller@getanggarandasarby');

Route::post('apiv8/insertanggarandasar','Apiv8Controller@insertanggarandasar');

Route::post('apiv8/updateanggarandasar','Apiv8Controller@updateanggarandasar');

Route::post('apiv8/deleteanggarandasar','Apiv8Controller@deleteanggarandasar');
//Pengesahan CRUDS
Route::post('apiv8/getpengesahan','Apiv8Controller@getpengesahan');

Route::post('apiv8/getpengesahanby','Apiv8Controller@getpengesahanby');

Route::post('apiv8/insertpengesahan','Apiv8Controller@insertpengesahan');

Route::post('apiv8/updatepengesahan','Apiv8Controller@updatepengesahan');

Route::post('apiv8/deletepengesahan','Apiv8Controller@deletepengesahan');

Route::post('apiv8/getkelompokkoperasi','Apiv8Controller@getkelompokkoperasi');

Route::post('apiv8/getsektorusaha','Apiv8Controller@getsektorusaha');

/*-----------End APIs Created by Timothy for Ministry report modules-----------------*/

/*----------- End APIs Created by Dimas for Indikator Kesehatan Koperasi -----------------*/

Route::post('apiv8/insertindikatorkesehatan','Apiv8Controller@insertindikatorkesehatan');
Route::post('apiv8/insertaspekpertanyaan','Apiv8Controller@insertaspekpertanyaan');
Route::post('apiv8/getscoreindikatorkesehatan','Apiv8Controller@getscoreindikatorkesehatan');


Route::post('apiv8/getindikatorkesehatan','Apiv8Controller@getindikatorkesehatan');
Route::post('apiv8/getdetilindikatorusaha','Apiv8Controller@getdetilindikatorusaha');

/*----------- End APIs Created by Dimas for Indikator Kesehatan Koperasi -----------------*/
