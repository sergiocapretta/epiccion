<?php
declare(strict_types=1);
/** E-Piccion - EmojiCrypt Engine v0.2a */
class EmojiCrypt{
public const VERSION='0.2a';
public const ALPHABET_SIZE=1024;
private string $password; private array $alphabet; private array $emojiToIndex=[];
public function __construct(string $password,array $alphabet){
 if(count($alphabet)<256) throw new InvalidArgumentException("Servono almeno 256 emoji.");
 $this->password=$password; $this->alphabet=array_values($alphabet);
 foreach($this->alphabet as $i=>$e){ if(isset($this->emojiToIndex[$e])) throw new InvalidArgumentException("Emoji duplicata"); $this->emojiToIndex[$e]=$i;}}
public function getVersion():string{return self::VERSION;}
public function getAlphabetSize():int{return count($this->alphabet);}
public function encrypt(string $text):string{
 $bytes=array_values(unpack("C*",$text)); $out=[];
 foreach($bytes as $p=>$b){$v=$b ^ $this->keyByte($p); $out[]=$this->alphabet[$v];}
 return implode(" ",$out);}
public function decrypt(string $cipher):string{
 $cipher=trim($cipher); if($cipher==='') return '';
 $sym=preg_split('/\s+/u',$cipher); $bytes=[];
 foreach($sym as $p=>$e){
  if(!isset($this->emojiToIndex[$e])) throw new RuntimeException("Emoji sconosciuta");
  $v=$this->emojiToIndex[$e];
  if($v>255) throw new RuntimeException("Emoji riservata alla futura Base1024");
  $bytes[]=$v ^ $this->keyByte($p);
 }
 return pack("C*",...$bytes);}
private function keyByte(int $pos):int{
 $h=hash("sha256",$this->password.":".$pos,true);
 return ord($h[0]);
}}
