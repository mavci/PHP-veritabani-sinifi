<?php

/*
 * PHP Veritabanı sınıfı
 * 2012 - phpr.org
 */

class vt {
	private $baglanti;
	private $hataGoster = true;
	public $karekter_seti = 'UTF8';

	function vt($kullanici, $sifre, $veritabani, $host = 'localhost') {
		$this->baglanti = mysql_connect($host, $kullanici, $sifre) or die('MYSQL ile bağlantı kurulamadı');
		if ($this->baglanti):
			mysql_select_db($veritabani, $this->baglanti) or die('( <b>' . $veritabani . '</b> ) isimli VERİTABANI BULUNAMADI');
			$this->sorgu('SET NAMES ' . $this->karekter_seti);
		endif;
	}

	function sorgu($sorgu) {
		$sorgu = mysql_query($sorgu, $this->baglanti);
		if (!$sorgu && $this->hataGoster)
			echo ('<p>HATA : <strong>' . mysql_error($this->baglanti) . '</strong></p>');

			return $sorgu;
	}

	function ekle($tablo, $veriler) {
		if (is_array($veriler)):
			$alanlar = array_keys($veriler);
			$alan = implode(',', $alanlar);
			$veri = '\'' . implode("', '", array_map(array($this, 'temizle'), $veriler)) . '\'';
		else:
			$parametreler = func_get_args();
			$tablo = array_shift($parametreler);
			$alan = $veri = null;
			$toplamParametre = count($parametreler) - 1;
			foreach ($parametreler as $NO => $parametre):
				$bol = explode('=', $parametre, 2);
				if ($toplamParametre == $NO):
					$alan .= $bol[0];
					$veri .= '\'' . $this->temizle($bol[1]) . '\'';
				else:
					$alan .= $bol[0] . ',';
					$veri .= '\'' . $this->temizle($bol[1]) . '\',';
				endif;
			endforeach;
		endif;

		$ekle = $this->sorgu('INSERT INTO ' . $tablo . ' (' . $alan . ') VALUES (' . $veri . ')');
		if ($ekle)
			return mysql_insert_id();
	}

	function tablo($sorgu) {
		$tablo = $this->sorgu($sorgu);
		$sonuc = array();
		while ($sonuclar = mysql_fetch_object($tablo))
			$sonuc[] = $sonuclar;
		return $sonuc;
	}

	function satir($sorgu) {
		$satir = $this->sorgu($sorgu);
		if ($satir)
			return mysql_fetch_object($satir);
	}

	function veri($sorgu) {
		$veri = $this->sorgu($sorgu);
		if ($veri):
			$sonuc = mysql_fetch_array($veri);
			return $sonuc[0];
		endif;
	}

	function sil($tablo, $kosul = null) {
		if ($kosul):
			if (is_array($kosul)):
				$kosullar = array();
				foreach ($kosul as $alan => $veri)
					$kosullar[] = $alan . '=\'' . $veri . '\'';
			endif;
			return $this->sorgu('DELETE FROM ' . $tablo . ' WHERE ' . (is_array($kosul) ? implode(' AND ', $kosullar) : $kosul));
		else:
			return $this->sorgu('TRUNCATE TABLE ' . $tablo);
		endif;
	}

	function duzenle($tablo, $deger, $kosul) {
		if (is_array($deger)):
			$degerler = array();
			foreach ($deger as $alan => $veri)
				$degerler[] = $alan . "='" . $this->temizle($veri) . "'";
		endif;

		if (is_array($kosul)):
			$kosullar = array();
			foreach ($kosul as $alan => $veri)
				$kosullar[] = $alan . "='" . $this->temizle($veri) . "'";
		endif;

		return $this->sorgu('UPDATE ' . $tablo . ' SET ' . (is_array($deger) ? implode(',', $degerler) : $deger) . ' WHERE ' . (is_array($kosul) ? implode(' AND ', $kosullar) : $kosul));
	}

	function temizle($veri) {
		return mysql_real_escape_string($veri);
	}
}

?>