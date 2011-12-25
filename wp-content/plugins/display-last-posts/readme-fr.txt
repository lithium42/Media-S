=== Display Last Post(s) ===
Contributors: none
Tags: last,post,posts,page,shortcode,include, article,article,dernier,derniers,
Requires at least: 1.5
Tested up to: 2.9
Stable tag: trunk
Donate Link: http://www.fb-graphic.com/dons/


Permet d'afficher le(s) dernier(s) article(s) à n'importe quel endroit du site/blog par l'utilisation soit d'un shortcode, soit d'un court code php (à placer dans les templates du thème).


== Description ==

**Display Last Posts** est un plugin vous permettant d'afficher le(s) dernier(s) article(s) (par défaut uniquement le dernier) à n'importe quel endroit de votre blog.

Deux solutions possibles : soit en intégrant un shortcode à vos articles ou à vos pages, via le CMS de Wordpress, soit en plaçant un code PHP dans un template de votre thème.

shortcode : [last-posts] ou [last-posts nb="5" cont="extrait"]
code PHP : <?php lastPosts('',''); ?> ou <?php lastPosts(5,'extrait'); ?>

Le shortcode à utiliser est : [last-posts]
Par défaut, seul le dernier article est affiché.
Arguments en options, ex. : [last-posts nb="5" cont="extrait"] - avec « nb » vous indiquez le nombre d'articles à afficher (s'afficheront par ordre décroissant) et avec « cont="extrait" » vous indiquez que le contenu affiché doit être uniquement l'extrait du ou des article(s).  

Le code PHP à placer dans les templates est : <?php lastPosts('',''); ?>
Par défaut, seul le dernier article est affiché.
Arguments en options, ex. : <?php lastPosts(5,'extrait'); ?> - le premier argument permet d'indiquer le nombre d'articles à afficher (s'afficheront par ordre décroissant) et le second argument permet d'afficher uniquement l'extrait du ou des article(s).

**Display Last Post(s)** est largement inspiré de la fonction sc_liste() proposée sur cette page :
http://www.webinventif.fr/wordpress-creation-de-shortcode-avance/


== Installation ==

Valide au minimum jusqu'à WordPress 2.9.2.

Téléchargez et décomprimez « display-last-post.zip », placez le dossier « display-last-post » dans votre dossier wp-content/plugins. Activez-le depuis le panneau « Extensions » du CMS.
Aucune configuration n'est requise.


== Stylisation CSS ==

Le code produit par **Display Last Post(s)** est inclus dans un div avec l'attribut class="display-last-post" (<div class="display-last-posts">) afin de faciliter la stylisation par CSS.


== Rapports de bugs et suggestions ==

Vous pouvez me joindre ici : http://www.fb-graphic.com/fb-contact.php
Merci de préciser [ Display Last Post ] dans le titre.


== Don ==

Si vous souhaitez faire un don, c'est ici : http://www.fb-graphic.com/dons/


== License ==

copyright (c) 2010 François Barret < www.fb-graphic.com >

La libre diffusion, l'utilisation et la modification de cette source sont autorisées à condition de respecter les clauses suivantes :

• L'intégralité de cette notice et l'intégralité de la licence doivent être conservées.
• Le nom et les références internet (adresses web) de l'auteur doivent être visible quel que soit le mode de diffusion et la modification de la source.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
