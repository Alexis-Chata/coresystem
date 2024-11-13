<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruta;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\ListaPrecio;
use Illuminate\Support\Facades\DB;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero, insertamos las rutas sin día de visita
        DB::insert("
        insert into `rutas`(`id`,`codigo`,`name`,`vendedor_id`,`empresa_id`,`lista_precio_id`) values
        (1,NULL,'JICAMARCA',9,1,1),
        (2,NULL,'J.C.M - HUANTA',3,1,1),
        (3,NULL,'JICAMARCA - VALLE',4,1,1),
        (4,NULL,'FRAGATAS',5,1,1),
        (5,NULL,'MARISCAL SEOANE',15,1,1),
        (6,NULL,'JICAMARCA STO DOMING',10,1,1),
        (7,NULL,'JOSE CARLOS MARIATEG',2,1,1),
        (8,NULL,'CASABLANCA - 10 OCTU',17,1,1),
        (9,NULL,'CONSTRUCTORES.',2,1,1),
        (10,NULL,'JICAMARCA - MEDIA LU',9,1,1),
        (11,NULL,'MONTENEGRO',1,1,1),
        (12,NULL,'RINCONADA - JICAMARC',3,1,1),
        (13,NULL,'MARIA JESUS',4,1,1),
        (14,NULL,'MOTUPE - MARISCAL CA',8,1,1),
        (15,NULL,'SANTA MARIA',25,1,1),
        (16,NULL,'ARRIBA PERï¿½ - 5 DE N',5,1,1),
        (17,NULL,'MOTUPE CIPRESES',8,1,1),
        (18,NULL,'ISRAEL - SR. DE LO M',17,1,1),
        (19,NULL,'STA MARIA - BOLIVAR',11,1,1),
        (20,NULL,'1 SETIEMBRE-IPD-UNIO',10,1,1),
        (21,NULL,'JUAN PABLO II',10,1,1),
        (22,NULL,'TOTORITA',3,1,1),
        (23,NULL,'SENATI LOS MONGOS',25,1,1),
        (24,NULL,'BANCHERO - SEGURO',5,1,1),
        (25,NULL,'PLANETAS-LAS FLORES',25,1,1),
        (26,NULL,'SAN CARLOS',12,1,1),
        (27,NULL,'SAN GABRIEL',11,1,1),
        (28,NULL,'BAYOVAR - COMISARIA',3,1,1),
        (29,NULL,'LAS FLORES - STO DOM',12,1,1),
        (30,NULL,'INTERMEDIO-MARISCAL',12,1,1),
        (31,NULL,'CASUARINAS - TERRAZA',9,1,1),
        (32,NULL,'CANTO CHICO',3,1,1),
        (33,NULL,'MARISCAL',4,1,1),
        (34,NULL,'CERRO CAMOTE ANX 8',5,1,1),
        (35,NULL,'SANTA ELIZABETH',4,1,1),
        (36,NULL,'CERRO CAMOTE - SAN P',8,1,1),
        (37,NULL,'CERRO CAMOTE - LA EN',15,1,1),
        (38,NULL,'PEDREGAL - JICAMARCA',11,1,1),
        (39,NULL,'METRO DEL 8',2,1,1),
        (40,NULL,'ANEXO 8 FINAL',18,1,1),
        (41,NULL,'HUASCAR (52)',9,1,1),
        (42,NULL,'CANTO REY',22,1,1),
        (43,NULL,'SAUCES',4,1,1),
        (44,NULL,'PROVIDENCIA',15,1,1),
        (45,NULL,'BAYOVAR-ATUSPARIA',10,1,1),
        (46,NULL,'10 DE OCTUBRE',10,1,1),
        (47,NULL,'HUIRACOCHA.',2,1,1),
        (48,NULL,'CAJA DE AGUA - COMIS',17,1,1),
        (49,NULL,'CHIMU - ZARATE',1,1,1),
        (50,NULL,'MANGOMARCA',1,1,1),
        (51,NULL,'CAMPOY CANDY',3,1,1),
        (52,NULL,'BELEN HUASCAR',4,1,1),
        (53,NULL,'TOTTUS PDRO 20',22,1,1),
        (54,NULL,'SAN HILARION - ARRIB',22,1,1),
        (55,NULL,'LAS FLORES - CHACARI',17,1,1),
        (56,NULL,'CAMPOY TOTUS',9,1,1),
        (57,NULL,'PAJATEN - ZARATE',11,1,1),
        (58,NULL,'HUACHIPA',15,1,1),
        (59,NULL,'CHIMU - SODIMAC',12,1,1),
        (60,NULL,'VILLA HERMOSA - HUAS',11,1,1),
        (61,NULL,'AYACUCHO',2,1,1),
        (62,NULL,'CELIMA',11,1,1),
        (63,NULL,'NORKYS LA HACIENDA',1,1,1),
        (64,NULL,'CARAPONGO',18,1,1),
        (65,NULL,'CERRO CAMOTE-POLICLI',17,1,1),
        (66,NULL,'TERRAZA DE CARAPONGO',5,1,1),
        (67,NULL,'LOS POSTES PROVIDENC',15,1,1),
        (68,NULL,'MIGUELGRAU JUAN P.II',9,1,1),
        (69,NULL,'CERRO CAMOTE-SANTA C',18,1,1),
        (70,NULL,'CAMPOY CALLE 8',18,1,1),
        (71,NULL,'MAYORISTA WINTER HUG',13,1,2),
        (72,NULL,'TERRASAS II',25,1,1),
        (73,NULL,'POSTES LAS FLORES',11,1,1),
        (74,NULL,'BAYOVAR 10 DE OCTUBR',10,1,1),
        (75,NULL,'VALLE MANTARO',2,1,1),
        (76,NULL,'ARMANDO MAYORISTA',16,1,2),
        (77,NULL,'ANGEL MALA-PISCO',7,1,2),
        (78,NULL,'VITARTE-CARAPONGO AN',21,1,1),
        (79,NULL,'STA ANITA MUNICIPALI',5,1,1),
        (80,NULL,'CHOSICA-QUIRIO',20,1,1),
        (81,NULL,'HORACIO BAJO',6,1,1),
        (82,NULL,'HUAYCAN ZONA T-S-M-L',6,1,1),
        (83,NULL,'ANGIE-HUAMANTANGA',21,1,1),
        (84,NULL,'SAN ANTONIO DE CARAP',12,1,1),
        (85,NULL,'STA.CRUZ LA FLORIDAJ',17,1,1),
        (86,NULL,'GLORIA GRANDE-SJ',12,1,1),
        (87,NULL,'MERCADOS CHARLI',1,1,2),
        (88,NULL,'CAMPOY MALECON',1,1,1),
        (89,NULL,'MONTENEGRO-PARADITA',21,1,1),
        (90,NULL,'VALLE SAGRADO-SARITA',21,1,1),
        (91,NULL,'GLORIA-CAJAMARQUILLA',18,1,1),
        (92,NULL,'UNION-ANGIE',21,1,1),
        (93,NULL,'SANTA ROSA HUACHIPA',8,1,1),
        (94,NULL,'HUAYARINGA-ï¿½Aï¿½A',6,1,1),
        (95,NULL,'STA ANITA PRADERAS H',8,1,1),
        (96,NULL,'ARCOR SUR CHICO',7,1,2),
        (97,NULL,'WINTER2 HUGO MAYORIS',13,1,2),
        (98,NULL,'CONO SUR-ANGEL MAYRS',7,1,2),
        (99,NULL,'ALEXANDER MERCADOS',12,1,2),
        (100,NULL,'PARIACHI',19,1,1),
        (101,NULL,'NIEVERIA',19,1,1),
        (102,NULL,'SANTA CLARA-MAILY',19,1,1),
        (103,NULL,'HUAYCAN ZONA A',6,1,1),
        (104,NULL,'TAGORE-WILLIAM',15,1,1),
        (105,NULL,'ATE-SAN GREGORIO',25,1,1),
        (106,NULL,'LOS ANGELES-ATE',24,1,1),
        (107,NULL,'STA ANITA-PRODUCTORE',25,1,1),
        (108,NULL,'MERCADOS LEAO2',10,1,2),
        (109,NULL,'LOS HALCONES-CHACLAC',24,1,1),
        (110,NULL,'SOL DE LAS VIï¿½AS',8,1,1),
        (111,NULL,'JARDINES SANTA CLARA',24,1,1),
        (112,NULL,'HUAYCAN ZONA HPL',24,1,1),
        (113,NULL,'PORTADA DE CERES',6,1,1),
        (114,NULL,'DINOES - ATE',19,1,1),
        (115,NULL,'LA MERCED - ATE',24,1,1),
        (116,NULL,'CIENEGULLA',19,1,1),
        (117,NULL,'CERES - ATE',6,1,1),
        (118,NULL,'MERCADOS H.JARA',9,1,2),
        (119,NULL,'SAN MATEO-MATUCANA',19,1,1),
        (120,NULL,'MERCADOS LUANA',23,1,2),
        (121,NULL,'MERCADOS-LUIS SANCHE',18,1,2),
        (122,NULL,'MERCADOS-FOSATI',19,1,2),
        (123,NULL,'MERCADOS-DIXON',6,1,2),
        (124,NULL,'MERCADOS-JHON BENITE',25,1,2),
        (125,NULL,'ANGEL CHINCHA',7,1,2),
        (126,NULL,'MERCADOS-GLADYS',22,1,2),
        (127,NULL,'MERCADOS-RONALD',4,1,2),
        (128,NULL,'MERCADOS PAUL LIZANA',8,1,2),
        (129,NULL,'MERCADOS WILLIAM',15,1,2);
        ");

        // Luego, actualizamos todas las rutas con días aleatorios
        DB::statement("
            UPDATE rutas
            SET dia_visita = ELT(FLOOR(1 + RAND() * 7),
                'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')
            WHERE dia_visita IS NULL
        ");
    }
}
