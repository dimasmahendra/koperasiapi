<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Indikatorkesehatankoperasi extends Model
{
    public function AspekManajemen($aspekpertanyaan)
    {
        //print_r($aspekpertanyaan);die();
        $data = array();
        foreach ($aspekpertanyaan as $key => $value) {
            if ($key == 'likuiditas') {
                $data[$key] = $value['Ya'] * 0.60;
            }
            if ($key == 'aktiva') {
                $data[$key] = $value['Ya'] * 0.30;
            }
            if ($key == 'permodalan') {
                $data[$key] = $value['Ya'] * 0.60;
            }
            if ($key == 'kelembagaan') {
                $data[$key] = $value['Ya'] * 0.50;
            }
            if ($key == 'manajemenumum') {
                $data[$key] = $value['Ya'] * 0.25;
            }
        }
        
        return $data;
    }

    public function Permodalan($permodalanTotalAset, $permodalanBerisiko, $permodalanModalSendiri)
    {
        if(ceil($permodalanTotalAset) == 0){
        	$data['permodalanTotalAset'] = 0;
        }
        elseif ((ceil($permodalanTotalAset) >= 1) && (ceil($permodalanTotalAset) <= 20)) {
        	$data['permodalanTotalAset'] = 1.5;
        }
        elseif ((ceil($permodalanTotalAset) >= 21) && (ceil($permodalanTotalAset) <= 40)) {
        	$data['permodalanTotalAset'] = 3.00;
        }
        elseif ((ceil($permodalanTotalAset) >= 41) && (ceil($permodalanTotalAset) <= 60)) {
        	$data['permodalanTotalAset'] = 6.00;
        }
        elseif ((ceil($permodalanTotalAset) >= 61) && (ceil($permodalanTotalAset) <= 80)) {
        	$data['permodalanTotalAset'] = 3;
        }
        elseif ((ceil($permodalanTotalAset) >= 81) && ($permodalanTotalAset <= 100)) {
        	$data['permodalanTotalAset'] = 1.5;
        }
        elseif ($permodalanTotalAset > 100){
            $data['permodalanTotalAset'] = 1.5;
        }

        if(ceil($permodalanBerisiko) == 0){
        	$data['permodalanBerisiko'] = 0;
        }
        elseif ((ceil($permodalanBerisiko) >= 1) && (ceil($permodalanBerisiko) <= 10)) {
        	$data['permodalanBerisiko'] = 0.6;
        }
        elseif ((ceil($permodalanBerisiko) >= 11) && (ceil($permodalanBerisiko) <= 20)) {
        	$data['permodalanBerisiko'] = 1.2;
        }
        elseif ((ceil($permodalanBerisiko) >= 21) && (ceil($permodalanBerisiko) <= 30)) {
        	$data['permodalanBerisiko'] = 1.8;
        }
        elseif ((ceil($permodalanBerisiko) >= 31) && (ceil($permodalanBerisiko) <= 40)) {
        	$data['permodalanBerisiko'] = 2.4;
        }
        elseif ((ceil($permodalanBerisiko) >= 41) && (ceil($permodalanBerisiko) <= 50)) {
        	$data['permodalanBerisiko'] = 3.0;
        }
        elseif ((ceil($permodalanBerisiko) >= 51) && (ceil($permodalanBerisiko) <= 60)) {
        	$data['permodalanBerisiko'] = 3.6;
        }
        elseif ((ceil($permodalanBerisiko) >= 61) && (ceil($permodalanBerisiko) <= 70)) {
        	$data['permodalanBerisiko'] = 4.2;
        }
        elseif ((ceil($permodalanBerisiko) >= 71) && (ceil($permodalanBerisiko) <= 80)) {
        	$data['permodalanBerisiko'] = 4.8;
        }
        elseif ((ceil($permodalanBerisiko) >= 81) && (ceil($permodalanBerisiko) <= 90)) {
        	$data['permodalanBerisiko'] = 5.4;
        }
        elseif ((ceil($permodalanBerisiko) >= 91) && ($permodalanBerisiko <= 100)) {
        	$data['permodalanBerisiko'] = 6.0;
        }
        elseif ($permodalanBerisiko > 100) {
            $data['permodalanBerisiko'] = 6.0;
        }

        if(ceil($permodalanModalSendiri) < 4){
        	$data['permodalanModalSendiri'] = 0;
        }
        elseif((ceil($permodalanModalSendiri) >= 4) && (ceil($permodalanModalSendiri) < 6)){
        	$data['permodalanModalSendiri'] = 1.5;
        }
        elseif((ceil($permodalanModalSendiri) >= 6) && (ceil($permodalanModalSendiri) < 8)){
        	$data['permodalanModalSendiri'] = 2.25;
        }
        elseif(ceil($permodalanModalSendiri) > 8){
        	$data['permodalanModalSendiri'] = 3.00;
        }
        return $data;
        //print_r($permodalanModalSendiri);die();
    }

    public function AktifaProduktif($aktifaVolume, $aktifaPinjaman, $aktifaCadangan, $aktifaBerisiko)
    {
        if(ceil($aktifaVolume) <= 25){
        	$data['aktifaVolume'] = 0;
        }
        elseif ((ceil($aktifaVolume) >= 26) && (ceil($aktifaVolume) <= 50)) {
        	$data['aktifaVolume'] = 5.00;
        }
        elseif ((ceil($aktifaVolume) >= 51) && (ceil($aktifaVolume) <= 75)) {
        	$data['aktifaVolume'] = 7.50;
        }
        elseif (ceil($aktifaVolume) >= 75) {
        	$data['aktifaVolume'] = 10.00;
        }

        if (ceil($aktifaPinjaman) == 0){
        	$data['aktifaPinjaman'] = 5.0;
        }
        elseif ((ceil($aktifaPinjaman) > 0) && (ceil($aktifaPinjaman) <= 10)){
        	$data['aktifaPinjaman'] = 4.0;
        }
        elseif ((ceil($aktifaPinjaman) > 10) && (ceil($aktifaPinjaman) <= 20)){
        	$data['aktifaPinjaman'] = 3.0;
        }
        elseif ((ceil($aktifaPinjaman) > 20) && (ceil($aktifaPinjaman) <= 30)){
        	$data['aktifaPinjaman'] = 2.0;
        }
        elseif ((ceil($aktifaPinjaman) > 30) && (ceil($aktifaPinjaman) <= 40)){
        	$data['aktifaPinjaman'] = 1.0;
        }
        elseif ((ceil($aktifaPinjaman) > 40) && (ceil($aktifaPinjaman) <= 45)){
        	$data['aktifaPinjaman'] = 0.5;
        }
        elseif (ceil($aktifaPinjaman) >= 45){
        	$data['aktifaPinjaman'] = 0;
        }

        if (ceil($aktifaCadangan) == 0){
        	$data['aktifaCadangan'] = 0;
        }
        elseif ((ceil($aktifaCadangan) >= 1) && (ceil($aktifaCadangan) <= 10)){
        	$data['aktifaCadangan'] = 0.5;
        }
        elseif ((ceil($aktifaCadangan) >= 11) && (ceil($aktifaCadangan) <= 20)){
        	$data['aktifaCadangan'] = 1.0;
        }
        elseif ((ceil($aktifaCadangan) >= 21) && (ceil($aktifaCadangan) <= 30)){
        	$data['aktifaCadangan'] = 1.5;
        }
        elseif ((ceil($aktifaCadangan) >= 31) && (ceil($aktifaCadangan) <= 40)){
        	$data['aktifaCadangan'] = 2.0;
        }
        elseif ((ceil($aktifaCadangan) >= 41) && (ceil($aktifaCadangan) <= 50)){
        	$data['aktifaCadangan'] = 2.5;
        }
        elseif ((ceil($aktifaCadangan) >= 51) && (ceil($aktifaCadangan) <= 60)){
        	$data['aktifaCadangan'] = 3.0;
        }
        elseif ((ceil($aktifaCadangan) >= 61) && (ceil($aktifaCadangan) <= 70)){
        	$data['aktifaCadangan'] = 3.5;
        }
        elseif ((ceil($aktifaCadangan) >= 71) && (ceil($aktifaCadangan) <= 80)){
        	$data['aktifaCadangan'] = 4.0;
        }
        elseif ((ceil($aktifaCadangan) >= 81) && (ceil($aktifaCadangan) <= 90)){
        	$data['aktifaCadangan'] = 4.5;
        }
        elseif ((ceil($aktifaCadangan) >= 91) && ($aktifaCadangan <= 100)){
        	$data['aktifaCadangan'] = 5.0;
        }
        elseif ($aktifaCadangan > 100){
            $data['aktifaCadangan'] = 5.0;
        }

        if (ceil($aktifaBerisiko) <= 21){
        	$data['aktifaBerisiko'] = 5.00;
        }
        elseif ((ceil($aktifaBerisiko) > 21) && (ceil($aktifaBerisiko) <= 25)){
        	$data['aktifaBerisiko'] = 3.75;
        }
        elseif ((ceil($aktifaBerisiko) > 26) && (ceil($aktifaBerisiko) <= 30)){
        	$data['aktifaBerisiko'] = 2.50;
        }
        elseif (ceil($aktifaBerisiko) > 30){
        	$data['aktifaBerisiko'] = 1.25;
        }
        return $data;
        //print_r($permodalanModalSendiri);die();
    }

    public function Efisiensi($efisiensiBruto, $efisiensiSHU, $efisiensiPelayanan)
    {
        if(ceil($efisiensiBruto) < 90){
        	$data['efisiensiBruto'] = 4.00;
        }
        elseif ((ceil($efisiensiBruto) >= 90) && (ceil($efisiensiBruto) <= 95)) {
        	$data['efisiensiBruto'] = 3.00;
        }
        elseif ((ceil($efisiensiBruto) >= 95) && (ceil($efisiensiBruto) <= 100)) {
        	$data['efisiensiBruto'] = 2.00;
        }
        elseif (ceil($efisiensiBruto) >= 100) {
        	$data['efisiensiBruto'] = 1.00;
        }

        if(ceil($efisiensiSHU) <= 40){
        	$data['efisiensiSHU'] = 4.00;
        }
        elseif ((ceil($efisiensiSHU) > 40) && (ceil($efisiensiSHU) <= 60)) {
        	$data['efisiensiSHU'] = 3.00;
        }
        elseif ((ceil($efisiensiSHU) > 60) && (ceil($efisiensiSHU) <= 80)) {
        	$data['efisiensiSHU'] = 2.00;
        }
        elseif (ceil($efisiensiSHU) > 80) {
        	$data['efisiensiSHU'] = 1.00;
        }

        if(ceil($efisiensiPelayanan) <= 5){
        	$data['efisiensiPelayanan'] = 2.00;
        }
        elseif ((ceil($efisiensiPelayanan) > 5) && (ceil($efisiensiPelayanan) <= 10)) {
        	$data['efisiensiPelayanan'] = 1.50;
        }
        elseif ((ceil($efisiensiPelayanan) > 10) && (ceil($efisiensiPelayanan) <= 15)) {
        	$data['efisiensiPelayanan'] = 1.00;
        }
        elseif (ceil($efisiensiPelayanan) > 15) {
        	$data['efisiensiPelayanan'] = 0;
        }        
        return $data;
        //print_r($permodalanModalSendiri);die();
    }

    public function Likuiditas($likuiditasKas, $likuiditasPinjaman)
    {
        if(ceil($likuiditasKas) <= 10){
        	$data['likuiditasKas'] = 2.50;
        }
        elseif ((ceil($likuiditasKas) > 10) && (ceil($likuiditasKas) <= 15)) {
        	$data['likuiditasKas'] = 10.00;
        }
        elseif ((ceil($likuiditasKas) > 15) && (ceil($likuiditasKas) <= 20)) {
        	$data['likuiditasKas'] = 5.00;
        }
        elseif (ceil($likuiditasKas) > 20) {
        	$data['likuiditasKas'] = 2.50;
        }

        if(ceil($likuiditasPinjaman) < 60){
        	$data['likuiditasPinjaman'] = 1.25;
        }
        elseif ((ceil($likuiditasPinjaman) >= 60) && (ceil($likuiditasPinjaman) < 70)) {
        	$data['likuiditasPinjaman'] = 2.50;
        }
        elseif ((ceil($likuiditasPinjaman) >= 70) && (ceil($likuiditasPinjaman) < 80)) {
        	$data['likuiditasPinjaman'] = 3.75;
        }
        elseif ((ceil($likuiditasPinjaman) >= 80) && ($likuiditasPinjaman < 90)) {
        	$data['likuiditasPinjaman'] = 5.00;
        }
        elseif ($likuiditasPinjaman > 90) {
            $data['likuiditasPinjaman'] = 5.00;
        }

        return $data;
        //print_r($permodalanModalSendiri);die();
    }

    public function KemandirianPertumbuhan($KemandirianPertumbuhanAset, $KemandirianPertumbuhanModalSendiri, $KemandirianPertumbuhanOperasional)
    {
        if(ceil($KemandirianPertumbuhanAset) < 5){
        	$data['KemandirianPertumbuhanAset'] = 0.75;
        }
        elseif ((ceil($KemandirianPertumbuhanAset) >= 5) && (round($KemandirianPertumbuhanAset) < 7.5)) {
        	$data['KemandirianPertumbuhanAset'] = 1.50;
        }
        elseif ((round($KemandirianPertumbuhanAset) >= 7.5) && (ceil($KemandirianPertumbuhanAset) < 10)) {
        	$data['KemandirianPertumbuhanAset'] = 2.25;
        }
        elseif (ceil($KemandirianPertumbuhanAset) >= 10) {
        	$data['KemandirianPertumbuhanAset'] = 3.00;
        }

        if(ceil($KemandirianPertumbuhanModalSendiri) < 3){
        	$data['KemandirianPertumbuhanModalSendiri'] = 0.75;
        }
        elseif ((ceil($KemandirianPertumbuhanModalSendiri) >= 3) && (round($KemandirianPertumbuhanModalSendiri) < 4)) {
        	$data['KemandirianPertumbuhanModalSendiri'] = 1.50;
        }
        elseif ((round($KemandirianPertumbuhanModalSendiri) >= 4) && (ceil($KemandirianPertumbuhanModalSendiri) < 5)) {
        	$data['KemandirianPertumbuhanModalSendiri'] = 2.25;
        }
        elseif (ceil($KemandirianPertumbuhanModalSendiri) > 5) {
        	$data['KemandirianPertumbuhanModalSendiri'] = 3.00;
        }

        if(ceil($KemandirianPertumbuhanOperasional) <= 100){
        	$data['KemandirianPertumbuhanOperasional'] = 0;
        }
        if(ceil($KemandirianPertumbuhanOperasional) > 100){
        	$data['KemandirianPertumbuhanOperasional'] = 4;
        }

        return $data;
        //print_r($permodalanModalSendiri);die();
    }

    public function JatidiriKoperasi($JatidiriKoperasiBrutto, $JatidiriKoperasiPEA)
    {
        if(ceil($JatidiriKoperasiBrutto) < 25){
        	$data['JatidiriKoperasiBrutto'] = 1.75;
        }
        elseif ((ceil($JatidiriKoperasiBrutto) >= 25) && (ceil($JatidiriKoperasiBrutto) < 50)) {
        	$data['JatidiriKoperasiBrutto'] = 3.50;
        }
        elseif ((ceil($JatidiriKoperasiBrutto) >= 50) && (ceil($JatidiriKoperasiBrutto) < 75)) {
        	$data['JatidiriKoperasiBrutto'] = 5.25;
        }
        elseif (ceil($JatidiriKoperasiBrutto) >= 75) {
        	$data['JatidiriKoperasiBrutto'] = 7;
        }

        if(ceil($JatidiriKoperasiPEA) < 5){
        	$data['JatidiriKoperasiPEA'] = 0;
        }
        elseif ((ceil($JatidiriKoperasiPEA) >= 5) && (round($JatidiriKoperasiPEA) < 7.5)) {
        	$data['JatidiriKoperasiPEA'] = 1.50;
        }
        elseif ((round($JatidiriKoperasiPEA) >= 7.5) && (ceil($JatidiriKoperasiPEA) < 10)) {
        	$data['JatidiriKoperasiPEA'] = 2.25;
        }
        elseif (ceil($JatidiriKoperasiPEA) >= 10) {
        	$data['JatidiriKoperasiPEA'] = 3;
        }

        return $data;
        //print_r($permodalanModalSendiri);die();
    }
}
