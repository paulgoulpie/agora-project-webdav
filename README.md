# agora-project-webdav - Webdav connector for Agora-Project

## Overview

agora-project-webdav is a plugin for [Agora-Project](https://www.agora-project.net) to implement webdav protocol for access files ressources in **read mode only** over [webdav](https://en.wikipedia.org/wiki/WebDAV) protocol.

This php project use [SabreDAV](https://en.wikipedia.org/wiki/SabreDAV) and works with v. 3.4.4 of [Agora-Project](https://www.agora-project.net) (not tested on other versions but can be functional).

### Installation

On your root folder Agora-Project installation (where contains this files and folder : `app`  `DATAS`  `docs`  `index.php`) type this command :

git clone https://github.com/paulgoulpie/agora-project-webdav.git webdav

### Usage

After install use webdav client : as Windows Explorer or Nautilus or [cadaver](http://www.webdav.org/cadaver/) command line tools and use with this url : 

 * http://*your_base_url_agora_project*/webdav/index.php

or

 * dav://*your_base_url_agora_project*/webdav/index.php    -  (for Nautilus)

## Issues And Bug Reports

To report a bug or make a request for new features, use the Issues Page in the agora-project-webdav Github project:

  * https://github.com/paulgoulpie/agora-project-webdav/issues

