using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class Credentials : BaseEntity
    {
        [Required, Unicode(36)]
        public string GeneratedToken { get; set; }

        [Unicode(Defaults.DefaultLongStringLength)]
        public string Tag { get; set; }
    }
}