<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../component/BaseModel.php";
/**
 * This class is used to prepare all data related to the asset components such
 * that the data can easily be displayed in the view of the component.
 */
class BMZSportModel extends BaseModel
{

    /** Constants *************************************************************/

    const bmz_sport_json_name = 'bmz_sport.json';
    const var_suffix = '_compare';

    const traces = '[{"name":"Individueles motifprofil","data_source":{"name":"bmz_evaluate_motive_R_1o57JPa46Cdtd2B_static","map":{"y":[{"name":"base_kon","op":"max"},{"name":"base_wetlei","op":"max"},{"name":"base_figaus","op":"max"},{"name":"base_stimm","op":"max"},{"name":"base_kogn","op":"max"},{"name":"base_allges","op":"max"},{"name":"base_bewerf","op":"max"},{"name":"base_bewerf","op":"max"}]}},"x":["Kontakt","Wettkampf<br>Leistung","Figur<br>Aussehen","Stimmungsregulation","Kognitive<br>Funktionsfähigkeit","Positive<br>Bewegungserfahrungen"],"type":"line"},{"name":"test","data_source":{"name":"bmz_evaluate_motive_R_1o57JPa46Cdtd2B_static","map":{"y":[{"name":"kon","op":"max"},{"name":"wetlei","op":"max"},{"name":"figaus","op":"max"},{"name":"stimm","op":"max"},{"name":"kogn","op":"max"},{"name":"allges","op":"max"},{"name":"bewerf","op":"max"},{"name":"base_bewerf","op":"max"}]}},"x":["Kontakt","Wettkampf<br>Leistung","Figur<br>Aussehen","Stimmungsregulation","Kognitive<br>Funktionsfähigkeit","Positive<br>Bewegungserfahrungen"],"type":"line"}]';
    /** Old */
    const old_result_legend = 'Kontakt", "Wettkampf<br>Leistung", "Figur<br>Aussehen", "Stimmungsregulation", "Kognitive<br>Funktionsfähigkeit", "Alltagskompetenz<br>Gesundheit", "Positive<br>Bewegungserfahrungen';
    const old_results = '{
        "result" : "<table class=\"table table-bordered\"><tbody><tr><td class=\"p-1\">Alltagskompetenz<br />Gesundheit</td><td class=\"p-1\">um im Alltag körperlich mobil zu bleiben. oder um körperlichen Beschwerden entgegenzuwirken.</td></tr><tr><td class=\"p-1\">Figur<br />Aussehen</td><td class=\"p-1\">wegen meiner Figur. oder um mein Gewicht zu regulieren.</td></tr><tr><td class=\"p-1\">Positive<br />Bewegungserfahrungen</td><td class=\"p-1\">weil Sport mir die Möglichkeit für schöne Bewegungen bietet. oder vor allem aus Freude an der Bewegung.</td></tr><tr><td class=\"p-1\">Kontakt</td><td class=\"p-1\">um durch den Sport neue Freunde zu gewinnen oder um mit anderen gesellig zusammen zu sein.</td></tr><tr><td class=\"p-1\">Wettkampf<br />Leistung</td><td class=\"p-1\">um mich mit anderen zu messen. Oder um sportliche Ziele zu erreichen.</td></tr><tr><td class=\"p-1\">Stimmungsregulation</td><td class=\"p-1\">um etwas gegen meine Energielosigkeit zu tun. oder um Stress abzubauen.</td></tr><tr><td class=\"p-1\">Kognitive<br />Funktionsfähigkeit</td><td class=\"p-1\">um geistig fit zu bleiben. oder um meine Denkfähigkeit zu erhalten.</td></tr></tbody></table>",
        "base_allges" : 112,
        "base_figaus" : 95,
        "base_stimm" : 105,
        "base_bewerf" : 98,
        "base_wetlei" : 90,
        "base_kogn" : 95,
        "base_kon" : 105
    }';
    /** Adolescence */
    const adolescence1 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 106.411,
            \"z_ind7_leikon_t1\" : 106.379,
            \"z_ind7_ablkat_t1\" : 102.16,
            \"z_ind7_figaus_t1\" : 88.786,
            \"z_ind7_ges_t1\" : 91.307,
            \"z_ind8_fit_t1\" : 105.848,
            \"z_ind7_aes_t1\" : 104.931,
            \"z_ind7_risspa_t1\" : 100.027
        }";
    const adolescence2 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 98.968,
            \"z_ind7_leikon_t1\" : 97.144,
            \"z_ind7_ablkat_t1\" : 101.649,
            \"z_ind7_figaus_t1\" : 99.378,
            \"z_ind7_ges_t1\" : 101.141,
            \"z_ind8_fit_t1\" : 108.218,
            \"z_ind7_aes_t1\" : 96.599,
            \"z_ind7_risspa_t1\" : 105.121
        }";
    const adolescence3 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 106.941,
            \"z_ind7_leikon_t1\" : 104.436,
            \"z_ind7_ablkat_t1\" : 102.822,
            \"z_ind7_figaus_t1\" : 100.588,
            \"z_ind7_ges_t1\" : 102.229,
            \"z_ind8_fit_t1\" : 111.863,
            \"z_ind7_aes_t1\" : 91.477,
            \"z_ind7_risspa_t1\" : 91.507
        }";
    const adolescence4 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 100.783,
            \"z_ind7_leikon_t1\" : 96.446,
            \"z_ind7_ablkat_t1\" : 101.96,
            \"z_ind7_figaus_t1\" : 101.053,
            \"z_ind7_ges_t1\" : 101.116,
            \"z_ind8_fit_t1\" : 111.491,
            \"z_ind7_aes_t1\" : 109.548,
            \"z_ind7_risspa_t1\" : 89.094
        }";
    const adolescence5 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 98.166,
            \"z_ind7_leikon_t1\" : 95.194,
            \"z_ind7_ablkat_t1\" : 97.192,
            \"z_ind7_figaus_t1\" : 114.584,
            \"z_ind7_ges_t1\" : 109.21,
            \"z_ind8_fit_t1\" : 112.498,
            \"z_ind7_aes_t1\" : 93.48,
            \"z_ind7_risspa_t1\" : 92.175
        }";
    const adolescence6 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Zweckfreie Sportbegeisterte\",
            \"z_ind7_kon_t1\" : 92.904,
            \"z_ind7_leikon_t1\" : 93.121,
            \"z_ind7_ablkat_t1\" : 110.548,
            \"z_ind7_figaus_t1\" : 109.131,
            \"z_ind7_ges_t1\" : 107.171,
            \"z_ind8_fit_t1\" : 111.219,
            \"z_ind7_aes_t1\" : 94.721,
            \"z_ind7_risspa_t1\" : 92.404
        }";

    /** Young adulthood */
    const young_adulthood1 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Gesundheits- und Figurorientierte\",
            \"z_ind7_kon_t1\" : 94.311,
            \"z_ind7_leikon_t1\" : 94.849,
            \"z_ind7_ablkat_t1\" : 103.964,
            \"z_ind7_figaus_t1\" : 112.218,
            \"z_ind7_ges_t1\" : 111.5,
            \"z_ind8_fit_t1\" : 112.378,
            \"z_ind7_aes_t1\" : 92.244,
            \"z_ind7_risspa_t1\" : 90.916
        }";
    const young_adulthood2 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Ablenkungssuchende, Figur-, Ästhetik- und Gesundheitsorientierte\",
            \"z_ind7_kon_t1\" : 89.934,
            \"z_ind7_leikon_t1\" : 91.878,
            \"z_ind7_ablkat_t1\" : 106.128,
            \"z_ind7_figaus_t1\" : 109.24,
            \"z_ind7_ges_t1\" : 109.312,
            \"z_ind8_fit_t1\" : 110.5,
            \"z_ind7_aes_t1\" : 104.526,
            \"z_ind7_risspa_t1\" : 88.981
        }";
    const young_adulthood3 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Kontaktfreudige WettkampfsportlerInnen\",
            \"z_ind7_kon_t1\" : 105.786,
            \"z_ind7_leikon_t1\" : 105.416,
            \"z_ind7_ablkat_t1\" : 101.805,
            \"z_ind7_figaus_t1\" : 94.654,
            \"z_ind7_ges_t1\" : 94.622,
            \"z_ind8_fit_t1\" : 106.237,
            \"z_ind7_aes_t1\" : 101.485,
            \"z_ind7_risspa_t1\" : 96.231
        }";
    const young_adulthood4 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Figur- und gesundheitsorientiere Stressregulierer\",
            \"z_ind7_kon_t1\" : 103.319,
            \"z_ind7_leikon_t1\" : 100.826,
            \"z_ind7_ablkat_t1\" : 105.101,
            \"z_ind7_figaus_t1\" : 105.294,
            \"z_ind7_ges_t1\" : 105.733,
            \"z_ind8_fit_t1\" : 111.986,
            \"z_ind7_aes_t1\" : 89.639,
            \"z_ind7_risspa_t1\" : 90.09
        }";
    const young_adulthood5 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Entspannungssuchende und gesundheitsorientierte Sportler\",
            \"z_ind7_kon_t1\" : 92.61,
            \"z_ind7_leikon_t1\" : 96.342,
            \"z_ind7_ablkat_t1\" : 107.061,
            \"z_ind7_figaus_t1\" : 99.831,
            \"z_ind7_ges_t1\" : 107.58,
            \"z_ind8_fit_t1\" : 111.879,
            \"z_ind7_aes_t1\" : 95.985,
            \"z_ind7_risspa_t1\" : 100.59
        }";
    const young_adulthood6 = "{
            \"result\" : \"<div class:'w-100 rounded bg-warning text-dark p-3 mb-3'><h5 class:'text-center'>«Zweckfrei» Sportbegeisterte</h5><p class:'text-justify'>Dieser Sporttyp ist aus Freude am Sport aktiv. Dabei steht der ästhetische Aspekt der sportlichen Aktivität im Vordergrund.</p></div><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-primary border border-primary flex-grow-1' style:'height: 2px;'> </div></div><div class:'d-flex justify-content-between'><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Der ästhetische Aspekt der sportlichen Aktivität steht bei ihm im Vordergrund. Er hat Freude an harmonischen Bewegungen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Sport in der Gruppe ist ihm wichtig. Er geniesst es, mit anderen Menschen zusammen zu sein und neue Kontakte zu knüpfen.</p><p class:'p-3 bg-primary text-white rounded' style:'width: 30%;'>Er übt Sport nicht aus einem bestimmten Grund aus («zweckfrei»). Er macht Sport «wegen des Sports selbst» und weniger wegen möglichen positiven Folgens wie z. B. der Gewichtskontrolle.</p></div><div class:'d-flex flex-column flex-grow-1 pdfStartNewPage'><div class:'d-flex align-items-center mb-3 mt-5'><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div><h5 class:'ml-3 mr-3'>Das zeichnet diesen Sporttyp aus</h5><div class:'rounded bg-success border border-success flex-grow-1' style:'height: 2px;'> </div></div><div class:'p-3 rounded border border-success mb-3'>Vielfalt ist gefragt. Sie kommen bei spielorientierten Aktivitäten (z. B. Badminton, Fussball, Tennis oder Streetball) auf Ihre Kosten. Spiele fördern den Kontakt mit anderen Menschen und das gesellige Miteinander. Zusätzlich ermöglichen gelungene Technik-Bewegungssequenzen (z. B. gut getimter Aufschlag im Tennis) Ästhetik zu erleben.</div><div class:'p-3 rounded border border-success mb-3'>Zu Ihnen passen zudem Ausdaueraktivitäten (z. B. Joggen, Biken, Schwimmen oder Skilanglaufen). Wenn Sie die Aktivität zu Zweit oder in der Gruppe durchführen, fördert dies den Kontakt. Zudem ermöglichen runde, rhythmische Bewegungsformen, beispielsweise ein leichtfüssiger Gang beim Joggen, das Erleben von Ästhetik.</div><div class:'p-3 rounded border border-success mb-3'>Vorzugsweise treiben Sie Sport zu Zweit oder in einer Gruppe – als Mitglied in einem Sportverein, einer Laufgruppe oder einfach nur mit Freunden.</div><div class:'p-3 rounded border border-success mb-3'>Ihnen könnten zudem tänzerische Aktivitäten mit gestalterischen Elementen gefallen. Gestalterisch-tänzerische Bewegungsformen wären z. B. Capoeira, Jazztanz, Salsa, oder zeitgenössischer Tanz. Wenn Sie die Aufmerksamkeit auf den Rhythmus der Bewegungen lenken oder Bewegungen zur Musik ausführen, wird Ihr Ästhetik-Erleben gefördert. Warum gehen Sie also nicht mal in einem Tanzstudio schnuppern?</div></div>\",
            \"type\" : \"Gesundheitsorientierte Ästheten\",
            \"z_ind7_kon_t1\" : 100.67,
            \"z_ind7_leikon_t1\" : 93.623,
            \"z_ind7_ablkat_t1\" : 102.276,
            \"z_ind7_figaus_t1\" : 102.395,
            \"z_ind7_ges_t1\" : 109.064,
            \"z_ind8_fit_t1\" : 112.31,
            \"z_ind7_aes_t1\" : 105.343,
            \"z_ind7_risspa_t1\" : 86.63
        }";


    /* Private Properties *****************************************************/

    /**
     * Survey reposnse array from qualtrics
     */
    private $survey_response;

    /**
     * Response id that comes from qualtrics survey
     */
    private $response_id;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $survey_response
     * An array with the result comming from a qualtrics survey
     * @param string $response_id
     * Response id that comes from qualtrics survey
     */
    public function __construct($services, $survey_response, $response_id)
    {
        parent::__construct($services);
        $this->survey_response = $survey_response;
        $this->response_id = $response_id;
    }

    /* Private Methods ***********************************************************/

    /**
     * Set variable values in the json object template
     * 
     * @param array $bmz_sport 
     * json object template
     * @param string $age_type 
     * the age category for the participent
     * @retval array
     * result log
     */
    private function set_variables_values($bmz_sport, $age_type)
    {
        $result_array = array();
        $result_array["result"] = true;
        foreach ($bmz_sport[$age_type]["variables"] as $variable_name => $variable_object) {
            if (!isset($this->survey_response[$variable_name])) {
                $result_array["error"] = "Variable: " . $variable_name . " is not set for age type: " . $age_type;
                $result_array["result"] = false;
            }
            $bmz_sport[$age_type]["variables"][$variable_name]['avrg'] = $this->survey_response[$variable_name];
            if (!$variable_object['special']) {
                // if the variable is not special add here
                $bmz_sport[$age_type]["values_array"][] = $this->survey_response[$variable_name];
            }
            // add all variables here
            $bmz_sport[$age_type]["values_array_special"][] = $this->survey_response[$variable_name];
        }
        $result_array['result'] = $bmz_sport;
        return $result_array;
    }

    /**
     * Calculate z_ind for all variables. It is based on: ((own value - avrg_value_all_variable) / std_dev_all_variables)
     * 
     * @param array $bmz_sport 
     * json object template
     * @param string $age_type 
     * the age category for the participent
     * @retval array
     * returns the json template filled with the calculated variables
     */
    private function calc_z_ind($bmz_sport, $age_type)
    {
        foreach ($bmz_sport[$age_type]["variables"] as $variable_name => $variable_object) {
            if ($variable_object['special']) {
                $bmz_sport[$age_type]["variables"][$variable_name]['z_ind'] = ($variable_object['avrg'] - $bmz_sport[$age_type]['avrg_special']) / $bmz_sport[$age_type]['sd_special'];
            } else {
                $bmz_sport[$age_type]["variables"][$variable_name]['z_ind'] = ($variable_object['avrg'] - $bmz_sport[$age_type]['avrg']) / $bmz_sport[$age_type]['sd'];
            }
            $bmz_sport[$age_type]["variables"][$variable_name]['z_ind_normalized'] = round($bmz_sport[$age_type]["variables"][$variable_name]['z_ind'] * 10 + 100);
        }
        return $bmz_sport;;
    }

    /**
     * Calculate all fqs for all profiles
     * 
     * @param array $bmz_sport 
     * json object template
     * @param string $age_type 
     * the age category for the participent
     * @retval array
     * returns the json template filled with the calculated variables
     */
    private function calc_profiles($bmz_sport, $age_type)
    {
        foreach ($bmz_sport[$age_type]["profiles"] as $profile_name => $profile_object) {
            $fqs = 0;
            foreach ($profile_object['profile_variables'] as $var_name => $var_object) {
                // normalize z_ind
                $bmz_sport[$age_type]["profiles"][$profile_name]['profile_variables'][$var_name]['z_ind_normalized'] = round($bmz_sport[$age_type]["profiles"][$profile_name]['profile_variables'][$var_name]['z_ind'] * 10 + 100);
                if (!(isset($var_object['exclude']) && $var_object['exclude'] === true)) {
                    // calc fqs only if variable is not excluded from calculations
                    $fqs = $fqs + pow(($bmz_sport[$age_type]['variables'][$var_name]['z_ind'] - $var_object['z_ind']), 2);
                }
            }
            $bmz_sport[$age_type]["profiles"][$profile_name]['fqs'] = $fqs;
        }
        return $bmz_sport;
    }

    /**
     * Set selected profile and assign the html feedback. Selected profile is the one with smallest fqs
     * 
     * @param array $bmz_sport 
     * json object template
     * @param string $age_type 
     * the age category for the participent
     * @retval array
     * returns the json template filled with the calculated variables
     */
    private function set_selected_profile($bmz_sport, $age_type)
    {
        $profiles = $bmz_sport[$age_type]["profiles"];
        array_multisort(array_column($profiles, 'fqs'), SORT_ASC, $profiles);
        foreach ($profiles as $profile_name => $profile) {
            // we need the first element only, it is the selected profile
            $bmz_sport[$age_type]["selected_profile"] = $profile_name;
            $bmz_sport[$age_type]["feedback_html"] = $profile["feedback_html"];
            foreach ($bmz_sport[$age_type]["variables"] as $variable_name => $variable_object) {
                $bmz_sport[$age_type]["variables"][$variable_name]['compare'] = $profile["profile_variables"][$variable_name]['z_ind_normalized'];
            }
            break;
        }
        return $bmz_sport;
    }

    /**
     * Generate the graph traces based on the selected profile and calculated variables
     * 
     * @param array $bmz_sport 
     * json object template
     * @param string $age_type 
     * the age category for the participent
     * @param strinf $code
     * the identity variable for the participent. It could be either the provided code or if none is provided then it is the RepsonseID from qualtrics
     * @retval array
     * returns the json template filled with the calculated variables
     */
    private function gen_graph_traces($bmz_sport, $age_type, $code)
    {
        // set selected profile
        $bmz_sport['traces'][0]['name'] = $bmz_sport[$age_type]['selected_profile'];
        // set datatable for trace1 and trace2
        $bmz_sport['traces'][0]['data_source']['name'] = qualtricsProjectActionAdditionalFunction_bmz_evaluate_motive . '_' . $code . '_static';
        $bmz_sport['traces'][1]['data_source']['name'] = qualtricsProjectActionAdditionalFunction_bmz_evaluate_motive . '_' . $code . '_static';
        // set maping and legend
        $bmz_sport['traces'][0]['data_source']['map']['y'] = []; // clear the example
        $bmz_sport['traces'][1]['data_source']['map']['y'] = []; // clear the example
        $bmz_sport['traces'][0]['x'] = []; // clear the example
        $bmz_sport['traces'][1]['x'] = []; // clear the example
        foreach ($bmz_sport[$age_type]["variables"] as $variable_name => $variable_object) {
            // set mapping
            $bmz_sport['traces'][0]['data_source']['map']['y'][] = array(
                "name" => $variable_name . $this::var_suffix,
                "op" => "max"
            );
            $bmz_sport['traces'][1]['data_source']['map']['y'][] = array(
                "name" => $variable_name,
                "op" => "max"
            );
            //set the legend
            $bmz_sport['traces'][0]['x'][] = $variable_object['label'];
            $bmz_sport['traces'][1]['x'][] = $variable_object['label'];;
        }
        return $bmz_sport;;
    }

    /**
     * Insert the generated data in the static tables. It should be one row.
     * @param array $data 
     * the data that will be used to insert the row
     * @retval string or false 
     * the result of the execution
     */
    private function insert_into_db($data)
    {
        $sql = "SELECT id FROM uploadTables WHERE name = :name";
        $name = qualtricsProjectActionAdditionalFunction_bmz_evaluate_motive . '_' . $data['code'];
        $has_table = $this->db->query_db_first($sql, array(":name" => $name));
        if ($has_table) {
            $res = $this->pp_delete_asset_file_static($name);
            if ($res !== true) {
                return $res;
            }
        }

        try {
            $this->db->begin_transaction();
            $id_table = $this->db->insert("uploadTables", array(
                "name" => $name
            ));
            if (!$id_table) {
                $this->db->rollback();
                return "postprocess: failed to create new data table";
            } else {
                if ($this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_uploadTables, $id_table) === false) {
                    $this->db->rollback();
                    return false;
                }
                $id_row = $this->db->insert("uploadRows", array(
                    "id_uploadTables" => $id_table
                ));
                if (!$id_row) {
                    $this->db->rollback();
                    return "postprocess: failed to add table rows";
                }
                foreach ($data as $col => $value) {
                    $id_col = $this->db->insert("uploadCols", array(
                        "name" => $col,
                        "id_uploadTables" => $id_table
                    ));
                    if (!$id_col) {
                        $this->db->rollback();
                        return "postprocess: failed to add table cols";
                    }
                    $res = $this->db->insert(
                        "uploadCells",
                        array(
                            "id_uploadRows" => $id_row,
                            "id_uploadCols" => $id_col,
                            "value" => $value
                        )
                    );
                    if (!$res) {
                        $this->db->rollback();
                        return "postprocess: failed to add data values";
                    }
                }
            }
            $this->db->commit();
            return 'Response for code : ' . $data['code'] . ' was successfully inserted in DB';
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Postprocessing DB data after deleting a static data file. The
     * corresponding DB-entries are removed.
     *
     * @param string $name
     *  The name of the file (without extension)
     * @retval mixed
     *  True on success, an error message on failure.
     */
    private function pp_delete_asset_file_static($name)
    {
        $res = $this->db->remove_by_fk("uploadTables", "name", $name);
        if (!$res) {
            return "postprocess: failed to remove old data values";
        }
        return true;
    }

    /* Public Methods *********************************************************/

    /**
     * Evaluate the survey reponses and insrt them into the database
     * @retval array
     * result log array
     */
    public function evaluate_survey()
    {
        $result = array();
        $result_array = array();
        $result_array["result"] = true;
        $bmz_sport_json_path = ASSET_SERVER_PATH . "/" . $this::bmz_sport_json_name; // it loads file bmz_sport.json from the assets folder. User can change it if needed.
        if (!file_exists($bmz_sport_json_path)) {
            $result_array["result"] = false;
            $result_array["error"] = "File: " . $bmz_sport_json_path . " does not exist.";
            return $result_array;
        }
        $file = file_get_contents($bmz_sport_json_path);
        $bmz_sport = json_decode($file, true);

        // set varaibles from the survey
        $result_array['code'] = isset($this->survey_response['code']) ? $this->survey_response['code'] : $this->response_id;
        $result_array['age'] = $this->survey_response['age'];
        $result_array['age_type'] = $this->survey_response['age_type'];
        $age_type = $result_array['age_type'];

        // calculate variable values from the survey response
        $variables_values = $this->set_variables_values($bmz_sport, $age_type);
        if (!$variables_values['result']) {
            $result_array["result"] = false;
            $result_array["error"] = $variables_values['error'];
            return $result_array;
        }
        $bmz_sport = $variables_values['result'];

        // calculate average and standard deviation
        $bmz_sport[$age_type]['avrg'] = array_sum($bmz_sport[$age_type]['values_array']) / count($bmz_sport[$age_type]['values_array']);
        $bmz_sport[$age_type]['avrg_special'] = array_sum($bmz_sport[$age_type]['values_array_special']) / count($bmz_sport[$age_type]['values_array_special']);
        $bmz_sport[$age_type]['sd'] = stats_standard_deviation($bmz_sport[$age_type]['values_array']);
        $bmz_sport[$age_type]['sd_special'] = stats_standard_deviation($bmz_sport[$age_type]['values_array_special']);

        if ($bmz_sport[$age_type]['sd'] == 0) {
            $result_array['feedback_html'] = "<b>Falls Sie sämtliche Fragen gleich beantwortet haben, kann Ihr Motivprofil nicht berechnet werden. Bitte füllen Sie den Fragebogen nochmals aus, wenn Sie eine Rückmeldung möchten.</b>";
            $result['std'] = "Standard deviation is 0";
        } else {
            // calculate z_ind (Intra-individual standardisation)
            $bmz_sport = $this->calc_z_ind($bmz_sport, $age_type);
            if (isset($bmz_sport[$age_type]["profiles"])) {
                // callculate profile
                $bmz_sport = $this->calc_profiles($bmz_sport, $age_type);
                // set selected profile
                $bmz_sport = $this->set_selected_profile($bmz_sport, $age_type);
            }

            // fill values that will be inserted in DB
            foreach ($bmz_sport[$age_type]["variables"] as $variable_name => $variable_object) {
                $result_array[$variable_name] = $variable_object['z_ind_normalized'];
                $result_array[$variable_name . $this::var_suffix] = $variable_object['compare'];
            }

            // generate graph traces based on the data
            $bmz_sport = $this->gen_graph_traces($bmz_sport, $age_type, $result_array['code']);
            $result_array['feedback_html'] = $bmz_sport[$age_type]['feedback_html'];
            $result_array['age_type'] = $age_type;
            $result_array['selected_profile'] = $bmz_sport[$age_type]['selected_profile'];
            $result_array['traces'] = json_encode($bmz_sport['traces']);
            if (DEBUG) {
                unset($bmz_sport[$age_type]['feedback_html']);
                foreach ($bmz_sport[$age_type]['profiles'] as $key => $value) {
                    unset($bmz_sport[$age_type]['profiles'][$key]['feedback_html']);
                    unset($bmz_sport[$age_type]['profiles'][$key]['label']);
                }
                $result_array['bmz_sport'] = json_encode($bmz_sport[$age_type], JSON_PRETTY_PRINT);
            }
        }

        $result['insert_into_db'] = $this->insert_into_db($result_array);
        if (isset($result_array["error"])) {
            $result['calc_error'] = $result_array['error'];
        }
        return $result;
    }
}
