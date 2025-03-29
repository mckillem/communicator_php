<?php

namespace ItNetwork\Forms;

enum Rules
{
	case Required;
	case MaxLength;
	case Password;
	case DateTime;
	case Pattern;
	case RequiredFile;
}
