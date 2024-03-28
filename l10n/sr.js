OC.L10N.register(
    "ldap_write_support",
    {
    "Could not find related LDAP entry" : "Не може да се пронађе одговарајућа LDAP ставка",
    "DisplayName change rejected" : "Одбијена је измена имена за приказ",
    "Write support for LDAP" : "Подршка уписа у LDAP",
    "Adds support for creating, manipulating and deleting users and groups on LDAP via Nextcloud" : "Додаје подршку за креирање, уређивање и брисање корисника и група на LDAP из Nextcloud",
    "The write support for LDAP enriches the LDAP backend with capabilities to manage the directory from Nextcloud.\n* create, edit and delete users\n* create, modify memberships and delete groups\n* prevent fallback to the local database backend (optional)\n* auto generate a user id (optional)\n* and more behavioral switches" : "Подршка уписа у LDAP обогађује LDAP позадински механизам могућностима за управљање директоријумом из Nextcloud.\n* креирање уређивање и брисање корисника\n* креирање, измена чланства и брисање група\n* спречавање употребе позадинског механизма локалне базе података у крајњој нужди (није обавезно)\n* аутоматско генерисање id корисника (није обавезно)\n* и још прекидача понашања",
    "Writing" : "Уписивање",
    "Switches" : "Прекидачи",
    "Prevent fallback to other backends when creating users or groups." : "Спречава употребу осталих позадинских механизама у крајњој нужди када се креирају корисници или групе.",
    "To create users, the acting (sub)admin has to be provided by LDAP." : "Да би се креирали корисници, LDAP мора да обезбеди дејствујућег (под)админа.",
    "A random user ID has to be generated, i.e. not being provided by the (sub)admin." : "Мора да се генерише насумични ID корисника, тј.  не сме да га обезбеди (под)админ.",
    "An LDAP user must have an email address set." : "LDAP кориниск мора да има постављену и-мејл адресу.",
    "Allow users to set their avatar" : "Дозволи да корисници поставе свој аватар",
    "User template" : "Кориснички шаблон",
    "LDIF template for creating users. Following placeholders may be used" : "LDIF шаблон за креирање корисника. Могу да се користе следећи чувари места",
    "the user id provided by the (sub)admin" : "id корисника који обезбеђује (под)админ",
    "the password provided by the (sub)admin" : "лозинка коју обезбеђује (под)админ",
    "the LDAP node of the acting (sub)admin or the configured user base" : "LDAP чвор дејствујућег (под)админа или конфигурисана база корисника",
    "Failed to set user template." : "Није успело постављае корисничког шаблона.",
    "Failed to set switch." : "Није успело постављање прекидача."
},
"nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);");