<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleQualtricsView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_moduleQualtrics.php";
    }

    /**
     * Render the navbar
     */
    public function output_navbar($title)
    {
        $res = array();
        $res[] = array(
            "title" => "Qualtrics",
            "active" => $title == "Qualtrics" ? "active" : "",
            "url" => $this->model->get_link_url("moduleQualtrics")
        );
        $res[] = array(
            "title" => "Projects",
            "active" => $title == "Projects" ? "active" : "",
            "url" => $this->model->get_link_url("moduleQualtricsProject")
        );
        $res[] = array(
            "title" => "Surveys",
            "active" => $title == "Surveys" ? "active" : "",
            "url" => $this->model->get_link_url("moduleQualtricsSurvey")
        );
        $navbar = new BaseStyleComponent("navigationBar", array(
            "items" => $res,
            "css" => "navbar-light bg-light"
        ));
        $navbar->output_content();
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        echo round(stats_cdf_normal(3.6, 3.43, 0.6, 1) * 100);
        echo 'Dashboard - TODO';
        echo '<p><strong>Pers&ouml;nliche R&uuml;ckmeldung f&uuml;r TeilnehmerIn mit dem Code: </strong><strong>dvst</strong></p>
<p><strong>&nbsp;</strong></p>
<p>Auf Basis Ihrer Antworten haben wir eine Rangreihe Ihrer pers&ouml;nlichen Charakterst&auml;rken erstellt. Damit Sie Ihre Ergebnisse verstehen k&ouml;nnen, erhalten Sie im Folgenden einige Hinweise, die von genereller Bedeutung sind. </p>
<p>Im Folgenden erhalten Sie eine Auflistung der 24 St&auml;rken. Dies ist Ihre pers&ouml;nliche Rangreihenfolge der Charakterst&auml;rken. Die erste St&auml;rke ist am wichtigsten be- ziehungsweise am typischsten f&uuml;r sie, die letzte eher unwichtig oder wenig charakteristisch. Die Forschung hat ergeben, dass Menschen meist zwischen drei und sieben f&uuml;r sie <em>cha- rakteristische St&auml;rken </em>aufweisen. Legen Sie Ihr Augenmerk deshalb auf die ersten St&auml;rken der Rangreihenfolge. Personen erfahren Zufriedenheit bei der Aus&uuml;bung dieser St&auml;rken, z.B. im Beruf (Harzer &amp; Ruch, 2012) oder bei Freizeitaktivit&auml;ten.</p>
<p>Die als Nummer 24 gereihte St&auml;rke ist die am geringsten ausgepr&auml;gte St&auml;rke (sie ist aber nicht als Schw&auml;che zu interpretieren).</p>
<p>Man nimmt an, dass jeder Mensch zwischen 3 und 7 &bdquo;Signaturst&auml;rken&ldquo; besitzt, also f&uuml;r eine</p>
<p>Person besonders zentral St&auml;rken, deren Aus&uuml;bung als erf&uuml;llend empfunden wird. Die in der</p>
<p>&bdquo;top 5 strengths&ldquo; besonders zu beachten. W&uuml;rden Sie den Fragebogen erneut ausf&uuml;llen, so k&ouml;nnte es sein, dass sich Ihre Rangreihenfolge mehr oder weniger ver&auml;ndert. Bei vielen Personen ist es jedoch so, dass die Auspr&auml;gungen ihrer Charakterst&auml;rken im Erwachsenenalter recht stabil bleiben.</p>
<p>Die r&uuml;ckgemeldeten Ergebnisse reflektieren eine Zusammenfassung <em>Ihrer VIA-IS- Selbstbeschreibung</em>. Sie selbst haben sich anhand der Fragen bzw. Aussagen beschrieben. Die Ergebnisse sind daher abh&auml;ngig davon, wie genau und ehrlich Sie die Fragen beantwortet haben und welches Bild Sie von sich selbst haben.</p>
<p>&nbsp;</p>
<p><strong>Wichtig! </strong>Nur weil eine Charakterst&auml;rke weiter unten aufgef&uuml;hrt ist, bedeutet das nicht, dass Sie diese nicht haben. Der Fragebogen misst auschliesslich die Auspr&auml;gung in St&auml;rken, nicht in Schw&auml;chen. Es ist also lediglich eine Rangfolge Ihrer St&auml;rken.</p>
<p>&nbsp;</p>
<table>
<tbody>
<tr>
<td width="55">
<p><strong>Rang</strong></p>
</td>
<td width="186">
<p><strong>Charakterst&auml;rke</strong></p>
</td>
</tr>
<tr>
<td width="55">
<p>1</p>
</td>
<td width="186">
<p>Kreativitaet</p>
</td>
</tr>
<tr>
<td width="55">
<p>2</p>
</td>
<td width="186">
<p>Neugier</p>
</td>
</tr>
<tr>
<td width="55">
<p>3</p>
</td>
<td width="186">
<p>Urteilsvermoegen</p>
</td>
</tr>
<tr>
<td width="55">
<p>4</p>
</td>
<td width="186">
<p>Liebe zum Lernen</p>
</td>
</tr>
<tr>
<td width="55">
<p>5</p>
</td>
<td width="186">
<p>Weisheit</p>
</td>
</tr>
<tr>
<td width="55">
<p>6</p>
</td>
<td width="186">
<p>Tapferkeit</p>
</td>
</tr>
<tr>
<td width="55">
<p>7</p>
</td>
<td width="186">
<p>Ausdauer</p>
</td>
</tr>
<tr>
<td width="55">
<p>8</p>
</td>
<td width="186">
<p>Authentizitaet</p>
</td>
</tr>
<tr>
<td width="55">
<p>9</p>
</td>
<td width="186">
<p>Enthusiasmus</p>
</td>
</tr>
<tr>
<td width="55">
<p>10</p>
</td>
<td width="186">
<p>Liebe</p>
</td>
</tr>
<tr>
<td width="55">
<p>11</p>
</td>
<td width="186">
<p>Freundlichkeit</p>
</td>
</tr>
<tr>
<td width="55">
<p>12</p>
</td>
<td width="186">
<p>Soziale Intelligenz</p>
</td>
</tr>
<tr>
<td width="55">
<p>13</p>
</td>
<td width="186">
<p>Teamfaehigkeit</p>
</td>
</tr>
<tr>
<td width="55">
<p>14</p>
</td>
<td width="186">
<p>Fairness</p>
</td>
</tr>
<tr>
<td width="55">
<p>15</p>
</td>
<td width="186">
<p>Fuehrungsvermoegen</p>
</td>
</tr>
<tr>
<td width="55">
<p>16</p>
</td>
<td width="186">
<p>Vergebungsbereitschaft</p>
</td>
</tr>
<tr>
<td width="55">
<p>17</p>
</td>
<td width="186">
<p>Bescheidenheit</p>
</td>
</tr>
<tr>
<td width="55">
<p>18</p>
</td>
<td width="186">
<p>Vorsicht</p>
</td>
</tr>
<tr>
<td width="55">
<p>19</p>
</td>
<td width="186">
<p>Selbstregulation</p>
</td>
</tr>
<tr>
<td width="55">
<p>20</p>
</td>
<td width="186">
<p>Sinn fuer das Schoene</p>
</td>
</tr>
<tr>
<td width="55">
<p>21</p>
</td>
<td width="186">
<p>Dankbarkeit</p>
</td>
</tr>
<tr>
<td width="55">
<p>22</p>
</td>
<td width="186">
<p>Hoffnung</p>
</td>
</tr>
<tr>
<td width="55">
<p>23</p>
</td>
<td width="186">
<p>Humor</p>
</td>
</tr>
<tr>
<td width="55">
<p>24</p>
</td>
<td width="186">
<p>Spiritualitaet</p>
</td>
</tr>
</tbody>
</table>
<p><strong>&nbsp;</strong></p>
<p><strong>&nbsp;</strong></p>
<p><strong>Beschreibung der St&auml;rken</strong></p>
<p>&nbsp;</p>
<p><strong>Kreativit&auml;t</strong></p>
<p>Kreative Menschen besitzen die n&ouml;tigen F&auml;higkeiten, um st&auml;ndig eine Vielzahl von verschiedenen originellen Ideen zu produzieren oder originelle Verhaltensweisen zu zeigen. Diese zeichnen sich dadurch aus, dass sie nicht nur innovativ und neu, sondern auch der Realit&auml;t angepasst sein m&uuml;ssen, damit sie den Menschen im Leben n&uuml;tzlich sind und ihnen weiterhelfen. Menschen mit ausgepr&auml;gter Kreativit&auml;t zeigen diese St&auml;rke meistens in mehreren Bereichen des Alltags auf, d.h. sie besitzen eine so genannte &bdquo;praktische Intelligenz&ldquo;.</p>
<p>&nbsp;</p>
<p><strong>Neugier</strong></p>
<p>Neugierige und interessierte Menschen haben ein ausgepr&auml;gtes Interesse an neuen Erfahrungen und sind sehr offen und flexibel bez&uuml;glich neuen, oft unerwarteten Situationen. Sie haben viele Interessen und finden an jeder Situation etwas Interessantes. Sie suchen aktiv nach Abwechslungen und Herausforderungen in ihrem t&auml;glichen Leben. Menschen k&ouml;nnen neugierig in Bezug auf einen spezifischen Bereich sein (z.B. Interesse an speziellen Tierarten) oder ein weitgefasstes Interesse an unterschiedlichen Dingen aufweisen.</p>
<p>&nbsp;</p>
<p><strong>Urteilsverm&ouml;gen</strong></p>
<p>Menschen mit einem stark ausgepr&auml;gten Urteilsverm&ouml;gen haben die F&auml;higkeit, Probleme und Gegebenheiten des Alltags aus unterschiedlichen Perspektiven zu betrachten, sie kritisch zu hinterfragen und Argumente f&uuml;r wichtige Entscheidungen zu entwickeln. Sie sind in der Lage, Informationen objektiv und kritisch zu beleuchten. Dabei orientieren sie sich an der Realit&auml;t.</p>
<p>&nbsp;</p>
<p><strong>Liebe zum Lernen</strong></p>
<p>Menschen mit einer ausgepr&auml;gten Wissbegierde zeichnen sich durch eine grosse Begeisterung f&uuml;r das Lernen neuer F&auml;higkeiten, Fertigkeiten und Wissensinhalte aus. Sie lieben es, neue Dinge zu lernen und sind bem&uuml;ht, sich st&auml;ndig weiterzubilden und zu entwickeln. Die Liebe zum Lernen kann sich auf einen spezifischen Themenbereich (z.B. Geschichte) beziehen oder auch ganz allgemein ausgepr&auml;gt sein. Die Wissbegierde widerspiegelt den Wunsch, immer mehr &uuml;ber das Leben und die Welt wissen zu wollen. Dabei wird das st&auml;ndige Lernen als eine Herausforderung betrachtet und es gibt kaum Menschen, die nicht mindestens in einem Bereich gerne lernen.</p>
<p>&nbsp;</p>
<p><strong>Weisheit</strong></p>
<p>Weise, weitsichtige bzw. tiefsinnige Menschen haben einen guten &Uuml;berblick und eine sinnvolle Sichtweise des Lebens. Sie besitzen die F&auml;higkeit, &uuml;ber das bisherige Leben eine sinnvolle Bilanz ziehen zu k&ouml;nnen. Dabei geht es um die Koordination des gelernten Wissens und der gemachten Erfahrungen eines Menschen, die zu seinem Wohlbefinden beitragen. Aus sozialer Perspektive betrachtet, k&ouml;nnen weise bzw. tiefsinnige Menschen anderen gut zuh&ouml;ren, Urteile abgeben und gute Ratschl&auml;ge erteilen. Von den Mitmenschen werden sie oft um Ratschl&auml;ge gebeten, weil sie eine Lebenseinstellung und Weltsicht haben, die f&uuml;r andere Leute (und sich selbst) Sinn macht.</p>
<p>&nbsp;</p>
<p><strong>Tapferkeit</strong></p>
<p>Tapfere und mutige Menschen verfolgen ihre Ziele und lassen sich dabei nicht von Schwierigkeiten und Hindernissen entmutigen. Tapferkeit und Mut k&ouml;nnen sich auf unterschiedliche Lebensbereiche beziehen. Bei dieser St&auml;rke handelt es sich um die F&auml;higkeit, etwas Positives und N&uuml;tzliches weiterzubringen, trotz drohender Gefahren. Sie erm&ouml;glicht einem Menschen, unbeliebte aber richtige Meinungen zu vertreten, sich einem Problem zu stellen, den &Auml;ngsten ins Gesicht zu schauen und sich gegen Ungerechtigkeiten zu wehren.</p>
<p><strong>&nbsp;</strong></p>
<p><strong>Ausdauer</strong></p>
<p>Ausdauer, Beharrlichkeit und Fleiss kennzeichnen Menschen, die alles zu Ende bringen wollen, was sie sich vorgenommen haben. Beharrlich streben sie nach ihren Zielen, geben nicht schnell auf, beenden was sie angefangen haben und lassen sich nicht st&auml;ndig ablenken. Mit Beharrlichkeit ist jedoch keine zwanghafte Verfolgung von unerreichbaren Zielen gemeint. Beharrliche Menschen passen sich flexibel und realistisch den jeweiligen Situationsbedingungen an, ohne perfektionistisch zu werden.</p>
<p>&nbsp;</p>
<p><strong>Autentizit&auml;t</strong></p>
<p>Ehrliche und authentische Menschen sind sich selbst und ihren Mitmenschen gegen&uuml;ber aufrichtig und ehrlich, halten ihre Versprechen und sind ihren Prinzipien treu. Sie legen Wert darauf, dass ihre Umgebung/Wirklichkeit nicht verf&auml;lscht wird. Sie sind f&auml;hig, f&uuml;r sich selbst die Verantwortung zu &uuml;bernehmen. Authentische Menschen handeln in &Uuml;bereinstimmung mit den eigenen Gedanken, Gef&uuml;hlen und &Uuml;berzeugungen.</p>
<p>&nbsp;</p>
<p><strong>Enthusiasmus</strong></p>
<p>Menschen mit einem ausgepr&auml;gten Enthusiasmus und Tatendrang sind voller Energie und Lebensfreude und weisen eine ausgepr&auml;gte Begeisterungsf&auml;higkeit f&uuml;r viele unterschiedliche Aktivit&auml;ten auf. Sie freuen sich auf jeden neuen Tag. Solche Menschen werden oft als energisch, flott, keck, munter und schwungvoll beschrieben. Sie setzen sich f&uuml;r ihre Aufgaben jeweils voll ein und bringen sie zu Ende.</p>
<p>&nbsp;</p>
<p><strong>Liebe</strong></p>
<p>Menschen mit ausgepr&auml;gter Bindungsf&auml;higkeit zeichnen sich dadurch aus, dass sie anderen Menschen ihre Liebe zeigen k&ouml;nnen und auch in der Lage sind, Liebe von anderen anzunehmen. Bei dieser St&auml;rke handelt es sich um die F&auml;higkeit, enge Beziehungen und Freundschaften mit Mitmenschen aufzubauen, die von Zuneigung und Gegenseitigkeit gekennzeichnet sind. Diese Beziehungen zeichnen sich vor allem durch gegenseitige Hilfeleistung, Akzeptanz und Verpflichtung aus.</p>
<p>&nbsp;</p>
<p><strong>Freundlichkeit</strong></p>
<p>Freundliche und grossz&uuml;gige Menschen zeichnen sich dadurch aus, dass sie sehr nett und hilfsbereit zu anderen Menschen sind und ihnen gerne einen Gefallen tun, auch wenn sie die andere Person nicht gut kennen. Sie lieben es, andere gl&uuml;cklich zu machen. Freundliches und grossz&uuml;giges Verhalten kann auf ganz unterschiedliche Art und Weise gezeigt werden (z.B. im Bus den eigenen Platz freigeben, bei den Hausaufgaben helfen, Blut spenden). Zentral an dieser St&auml;rke ist die Wertsch&auml;tzung, die man anderen Menschen zukommen l&auml;sst.</p>
<p>&nbsp;</p>
<p><strong>Soziale Intelligenz</strong></p>
<p>Menschen unterscheiden sich in der F&auml;higkeit, wichtige soziale Informationen, wie z.B. Gef&uuml;hle, wahrzunehmen und zu verarbeiten. Sozial intelligente Menschen kennen ihre Motive und Gef&uuml;hle und sie nehmen auch Unterschiede zwischen Menschen vor allem in Bezug zu deren Stimmungen, Motivationen und Absichten wahr. Sie kennen auch ihre eigenen Interessen und F&auml;higkeiten und sind in der Lage, sie zu f&ouml;rdern. Ein wichtiges Merkmal besteht darin, sich der jeweiligen Situation anzupassen.</p>
<p>&nbsp;</p>
<p><strong>Teamf&auml;higkeit</strong></p>
<p>Menschen mit dieser St&auml;rke zeichnen sich durch ihre Teamf&auml;higkeit und Verbundenheit gegen&uuml;ber ihrer Gruppe aus. Sie k&ouml;nnen dann am besten arbeiten, wenn sie Teil einer Gruppe sind. Die Gruppenzugeh&ouml;rigkeit wird sehr hoch bewertet. Die eigenen Interessen werden meistens zugunsten der Gruppe zur&uuml;ckgesteckt. Teamf&auml;hige Menschen tragen oft eine soziale Verantwortung. Auch die getroffenen Entscheidungen der Gruppe werden respektiert und vor die eigenen Meinungen gestellt.</p>
<p><strong>&nbsp;</strong></p>
<p><strong>Fairness</strong></p>
<p>Faire Menschen zeichnen sich durch einen ausgepr&auml;gten Sinn f&uuml;r Gerechtigkeit und Gleichheit aus. Jede Person wird gleich und fair behandelt, ungeachtet dessen, wer und was sie ist. Sie lassen sich in Entscheidungen nicht durch pers&ouml;nliche Gef&uuml;hle beeinflussen, sondern versuchen, allen eine Chance zu geben. Die Bereitschaft zu Kompromissen (Mittelwegen) sowie das Zugebenk&ouml;nnen von eigenen Fehlern werden als wichtige Merkmale dieser St&auml;rke bezeichnet.</p>
<p>&nbsp;</p>
<p><strong>F&uuml;hrungsverm&ouml;gen</strong></p>
<p>Menschen mit einem ausgepr&auml;gten F&uuml;hrungsverm&ouml;gen besitzen die F&auml;higkeit, einer Gruppe zu helfen gut miteinander zu arbeiten trotz unterschiedlichster Personen in der Gruppe. Ebenso zeichnen sie sich durch gute Planungs- und Organisationsf&auml;higkeiten von Gruppenaktivit&auml;ten aus und k&ouml;nnen auch schwierige Entscheidungen treffen. Sie schaffen ein arbeitsf&ouml;rderndes Klima, unterst&uuml;tzen die gemeinsame Arbeit an Gruppenzielen und f&ouml;rdern das Zugeh&ouml;rigkeitsgef&uuml;hl in der Gruppe, indem sie unterschiedliche Meinungen der Gruppenmitglieder einbeziehen k&ouml;nnen.</p>
<p>&nbsp;</p>
<p><strong>Vergebungsbereitschaft</strong></p>
<p>Menschen mit dieser St&auml;rke sind eher in der Lage, Vergangenes (z.B. einen Streit oder eine Meinungsverschiedenheit) ruhen zu lassen und einen Neuanfang zu wagen und k&ouml;nnen bis zu einem gewissen Punkt Verst&auml;ndnis aufbringen f&uuml;r die schlechte Behandlung durch andere Menschen. Sie geben ihren Mitmenschen eine Chance zur Wiedergutmachung. Der Prozess des Vergebens bzw. des Verzeihens beinhaltet heilsame und f&ouml;rderliche Ver&auml;nderungen von Gedanken, Gef&uuml;hlen und Verhaltensweisen bei Menschen, die von anderen verletzt wurden.</p>
<p>&nbsp;</p>
<p><strong>Bescheidenheit</strong></p>
<p>Bescheidene Menschen zeichnen sich dadurch aus, dass sie nicht mit ihren Erfolgen prahlen, nicht gerne in der Menge auffallen und auch nicht die Aufmerksamkeit auf sich ziehen wollen. Sie ziehen es vor, andere reden zu lassen. Bescheidene Menschen k&ouml;nnen Fehler und M&auml;ngel zugeben. Bescheidenheit kann sich auch auf eine innere Haltung beziehen, die sich dadurch kennzeichnet, dass man sich nicht als Zentrum der Welt betrachtet.</p>
<p>&nbsp;</p>
<p><strong>Vorsicht</strong></p>
<p>Kluge und vorsichtige Menschen zeichnen sich dadurch aus, dass sie Entscheidungen sorgf&auml;ltig treffen, &uuml;ber m&ouml;gliche Konsequenzen vor dem Sprechen und Durchf&uuml;hren nachdenken und Recht von Unrecht unterscheiden k&ouml;nnen. Sie vermeiden gef&auml;hrliche k&ouml;rperliche Aktivit&auml;ten, was aber nicht heisst, dass sie neue Erfahrungen meiden. Sie werden von ihren Mitmenschen oft als vorsichtig im positiven Sinne bezeichnet. Mit ihren F&auml;higkeiten sind kluge Menschen in der Lage, l&auml;ngerfristige Ziele sorgf&auml;ltig zu planen und zu verfolgen, ohne sich &bdquo;kopflos&ldquo; in ein Abenteuer zu st&uuml;rzen.</p>
<p>&nbsp;</p>
<p><strong>Selbstregulation</strong></p>
<p>Menschen mit ausgepr&auml;gter Selbstregulation kontrollieren ihre Gef&uuml;hle und ihr Verhalten in allen Situationen, z.B. ein Geheimnis f&uuml;r sich behalten, sich gesund ern&auml;hren, regelm&auml;ssig Sport treiben, rechtzeitig Aufgaben erledigen. Sie zeichnen sich dadurch aus, dass sie l&auml;ngerfristigen Erfolg dem kurzfristigen vorziehen. Sie weisen eine starke Selbstdisziplin auf und merken aber gleichzeitig auch, wann es genug ist.</p>
<p><strong>&nbsp;</strong></p>
<p><strong>Sinn f&uuml;r das Sch&ouml;ne</strong></p>
<p>Menschen, die in verschiedenen Lebensbereichen (wie z.B. Musik, Kunst, Natur, Sport, Wissenschaft) Sch&ouml;nes bewusst wahrnehmen, wertsch&auml;tzen und sich dar&uuml;ber freuen k&ouml;nnen, haben einen ausgepr&auml;gten Sinn f&uuml;r das Sch&ouml;ne. Sie nehmen im Alltag sch&ouml;ne Dinge wahr, die von anderen &uuml;bersehen oder nicht beachtet werden. Beim Anblick der Sch&ouml;nheit der Natur oder von Kunst empfinden sie tiefe Gef&uuml;hle der Ehrfurcht und der Verwunderung und sind oft sprachlos. Es kommt auch vor, dass solche Menschen selber etwas Sch&ouml;nes schaffen, wie z.B. ein Bild malen.</p>
<p>&nbsp;</p>
<p><strong>Dankbarkeit</strong></p>
<p>Dankbare Menschen zeichnen sich dadurch aus, dass sie sich bewusst sind &uuml;ber all die vielen Dinge in ihrem Leben, die nicht selbstverst&auml;ndlich sind. Sie nehmen sich die Zeit, ihre Dankbarkeit Menschen gegen&uuml;ber auszudr&uuml;cken. Wenn sie ein Geschenk bekommen, zeigen sie ihre Dankbarkeit. Sie realisieren, dass sie im Leben mit vielem gesegnet (beschenkt) sind. Die Dankbarkeit kann sich sowohl auf Menschen beziehen als auch auf nichtmenschliche Dinge (z.B. Tiere, Natur, Gott). Man kann die Dankbarkeit als gef&uuml;hlvolle Antwort auf ein &bdquo;Geschenk&ldquo; betrachten.</p>
<p>&nbsp;</p>
<p><strong>Hoffnung</strong></p>
<p>Zuversichtliche und optimistische Menschen haben grunds&auml;tzlich eine positive Einstellung gegen&uuml;ber der Zukunft. Sie k&ouml;nnen auch dann noch etwas positiv sehen, wenn es f&uuml;r andere negativ erscheint. Sie hoffen das Beste f&uuml;r die Zukunft und tun ihr M&ouml;glichstes, um ihre Ziele zu erreichen. Sie haben dabei ein klares Bild, was sie sich f&uuml;r die Zukunft w&uuml;nschen und wie sie sich die Zukunft vorstellen. Und wenn es mal nicht klappt, dann versuchen sie trotz Herausforderungen oder R&uuml;ckschl&auml;gen hoffnungsvoll in die Zukunft zu blicken.</p>
<p>&nbsp;</p>
<p><strong>Humor</strong></p>
<p>Humorvolle und heitere Menschen lachen gerne und bringen andere Menschen gerne zum L&auml;cheln oder zum Lachen. Sie versuchen ihre Freunde und Freundinnen aufzuheitern, wenn diese in einer bedr&uuml;ckten Stimmung sind. Menschen mit einem ausgepr&auml;gten Sinn f&uuml;r Humor versuchen in allen m&ouml;glichen Situationen, Spass zu haben und versuchen alles was sie machen, mit ein bisschen Humor anzugehen. Humorvollen Menschen gelingt es auch, verschiedene Situationen von einer leichteren Seite her zu betrachten.</p>
<p>&nbsp;</p>
<p><strong>Religi&ouml;sit&auml;t/Spiritualit&auml;t</strong></p>
<p>Religi&ouml;se bzw. gl&auml;ubige Menschen haben bestimmte &Uuml;berzeugungen &uuml;ber den h&ouml;heren Sinn und Zweck des Universums/der Welt. Sie glauben an eine h&ouml;here Macht bzw. an einen Gott. Ihre religi&ouml;sen &Uuml;berzeugungen beeinflussen ihr Denken, Handeln und F&uuml;hlen und k&ouml;nnen auch in schwierigen Zeiten eine Quelle des Trostes und der Kraft sein. Religi&ouml;se Menschen praktizieren ihre Religion, was sich durch unterschiedliche Verhaltensweisen zeigen kann, z.B. beten, meditieren, Kirchenbesuch oder Besinnung.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>';
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        //dummy
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }
}
?>
