<?php 
require_once( 'errorHandling.php' );
require_once( 'HTMLOutput.php' );
$ho = new HtmlOutput();
$ho->printHeader(array('Terms Of Service'),array('about','faq','contact'));

$ho->printParagraph('Thank you for your interest in Music Routes. These Terms of Service
("Agreement") govern your use of Music Routes. Please read this
Agreement in full before using the Music Routes service. By using the service, you agree to the following terms and
conditions. Only individuals who have agreed to the terms and
conditions of this Agreement are authorized to access and use the
Music Routes service.');

$ho->printSectionHeader('QUALIFICATIONS FOR A SUBSCRIPTION');
$ho->printParagraph('You must be legally capable to enter into a contract.');

$ho->printParagraph('You are responsible for obtaining and maintaining at your expense all
equipment and services needed to access Music Routes, including all
communications, data and Internet access charges.');

$ho->printSectionHeader('PROPRIETARY RIGHTS');

$ho->printParagraph('The Music Routes software, information, artwork,
text, media or pictures (collectively, "Materials") provided on
Music Routes are owned by Music Routes or third party suppliers and are
protected by U.S. and international copyright laws. You agree that
these copyright laws and proprietary interests limit your rights to
the Materials.  Text submitted by Music Routes users to Music Routes
becomes the property of Music Routes.');

$ho->printSectionHeader('USE OF THE SERVICE');
$ho->printParagraph('Materials cannot be modified, transferred, copied
or distributed to other platforms or devices, including computer hard
drives or any other storage medium. You may not reverse engineer,
decompile, disassemble, or otherwise attempt to discover the source
code or tamper with any part of Music Routes, including its security
components, special rules or other protection applications for any
reason whatsoever. You agree to abide by the rules and policies
established from time to time by Music Routes. Such rules and policies
will be applied generally in a nondiscriminatory manner to users of
Music Routes. You further agree that you will not attempt to modify the
software or any of the usage rules for any reason whatsoever,
including for the purpose of disguising or changing ownership of the
Materials. You expressly agree that you will use the Music Routes
service only for lawful purposes and in accordance with this
Agreement, and that you will not use the service to violate any law,
regulation or ordinance or any right of Music Routes or any third
party, including without limitation, any right of privacy, publicity,
copyright, trademark, or patent.');

$ho->printSectionHeader('LICENSE');

$ho->printParagraph('Subject to the terms of this Agreement, Music Routes provides
you with a limited, personal, nonexclusive and nontransferable license
to access and use Music Routes and its software for personal use. You
may not sub-license or charge others to use or access Music Routes. You
may not make derivative works from the Music Routes software or
Materials. You may not modify the Music Routes software or use it in
any way not expressly authorized by this Agreement.');

$ho->printSectionHeader('DISCLAIMER OF WARRANTY');
$ho->printParagraph('YOU AGREE THAT YOUR USE OF THE MUSICROUTES
SERVICE, SOFTWARE AND MATERIALS (collectively, the "INFORMATION") IS
AT YOUR OWN RISK. MUSIC ROUTES, RICHARD TROTT, EMPLOYEES, PARTNERS,
AGENTS, REPRESENTATIVES AND THIRD-PARTY SUPPLIERS (collectively, the
"MUSIC ROUTES PROVIDERS") PROVIDE THE INFORMATION "AS IS", "WITH ALL
FAULT" AND "AS AVAILABLE", WITHOUT WARRANTIES OF ANY KIND. THE
MUSIC ROUTES PROVIDERS EXPRESSLY DISCLAIM ANY REPRESENTATIONS AND
WARRANTIES, INCLUDING WITHOUT LIMITATION, IMPLIED WARRANTIES OF
ACCURACY, QUALITY, SECURITY, MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE OR NEED, UNLESS SUCH WARRANTIES ARE LEGALLY INCAPABLE OF
EXCLUSION. THE MUSIC ROUTES PROVIDERS MAKE NO WARRANTY (a) THAT THE
MUSIC ROUTES SERVICE AND SOFTWARE WILL MEET YOUR REQUIREMENTS, (b) THAT
THE SERVICE WILL BE UNINTERRUPTED OR FREE OF DEFECTS, ERRORS OR
VIRUSES, (c) THAT ERRORS OR DEFECTS IN THE SERVICE WILL BE CORRECTED,
(d) THAT YOU WILL BE ABLE TO ACCESS OR USE MUSIC ROUTES AT ALL TIMES OR
PLACES, OR (e) AS TO THE RELIABILITY, ACCURACY, QUALITY OR
APPROPRIATENESS OF THE MATERIALS AND CONTENT.');

$ho->printSectionHeader('LIMITATION OF LIABILITY');
$ho->printParagraph('THE MUSIC ROUTES PROVIDERS SHALL NOT BE LIABLE
FOR CONSEQUENTIAL, SPECIAL OR PUNITIVE DAMAGES, INCLUDING, WITHOUT
LIMITATION, DAMAGES RELATED TO LOST PROFITS OR LOST OPPORTUNITIES,
LOST DATA, LOSS OF GOODWILL, WORK STOPPAGE, COMPUTER FAILURE OR
MALFUNCTION, OR ANY OTHER COMMERCIAL DAMAGES OR LOSSES, EVEN IF THE
MUSIC ROUTES PROVIDERS HAD BEEN ADVISED OF THE POSSIBILITY THEREOF AND
REGARDLESS OF THE LEGAL OR EQUITABLE THEORY UPON WHICH THE CLAIM IS
BASED. BECAUSE SOME STATES OR JURISDICTIONS DO NOT ALLOW THE EXCLUSION
OR THE LIMITATION OF LIABILITY FOR CONSEQUENTIAL OR INCIDENTAL
DAMAGES, IN SUCH STATES OR JURISDICTIONS, MUSIC ROUTES AND ITS
SUPPLIERS\' LIABILITY IN SUCH STATE OR JURISDICTION SHALL BE LIMITED TO
THE EXTENT PERMITTED BY LAW.');

$ho->printSectionHeader('CHANGES TO THE SERVICE');
$ho->printParagraph('Music Routes has the right at any time to
change, modify, add to, discontinue or retire the Music Routes service,
or any portion or feature of the service, without notice to
you.');

$ho->printSectionHeader('NO SUPPORT');
$ho->printParagraph('Music Routes is under no obligation to provide assistance
or support for your use of Music Routes or to provide you with any
error corrections, updates, upgrades, bug fixes and/or enhancements
for Music Routes.');

$ho->printSectionHeader('PRIVACY');
$ho->printParagraph('Music Routes does not require you to provide any registration
information that would identify you personally to access
Music Routes. However, Music Routes will collect the IP address that you
use to access Music Routes. Your IP address is a number that is used by
computers on the network to identify your computer so that data can be
sent to you. Music Routes servers will automatically collect and store
the dates and times that your IP address logs into the service, as
well as information about the pages viewed ("Usage
Information"). Music Routes may use aggregate data concerning users\'
use of Music Routes in order to determine how its users use different
parts of the service, and to improve the features and functionality of
the service.');

$ho->printSectionHeader('TERMINATION');
$ho->printParagraph('Music Routes may terminate, without notice, your access to
Music Routes in the event of a violation of this Agreement. In the
event of any termination of this Agreement, the restrictions on your
use of the service as set forth herein shall survive such termination,
and you agree to be bound by these terms.');

$ho->printSectionHeader('PROCEDURE FOR MAKING CLAIMS OF COPYRIGHT INFRINGEMENT');
$ho->printCopyrightParagraph();
$ho->printContactParagraph();
	 
$ho->printFooter()
?>